<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RechargeRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RechargeRequestController extends Controller
{
    public function index(Request $request)
    {
        $recharges = RechargeRequest::with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhere('payment_reference', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($request->status, ['pending', 'approved', 'rejected'], true),
                fn ($query) => $query->where('status', $request->status)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.recharges.index', compact('recharges'));
    }

    public function show(RechargeRequest $recharge)
    {
        $recharge->load(['user', 'reviewer', 'walletTransaction']);

        return view('admin.recharges.show', compact('recharge'));
    }

    public function approve(Request $request, RechargeRequest $recharge)
    {
        DB::transaction(function () use ($request, $recharge) {
            $lockedRecharge = RechargeRequest::whereKey($recharge->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRecharge->status !== 'pending') {
                abort(409, 'This recharge request has already been processed.');
            }

            // Lock the user row before ensuring their wallet exists.
            $user = User::whereKey($lockedRecharge->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'lifetime_credited' => 0, 'lifetime_debited' => 0]
            );

            $wallet = Wallet::whereKey($wallet->id)
                ->lockForUpdate()
                ->firstOrFail();

            $amountCents = (int) round((float) $lockedRecharge->amount * 100);
            $beforeCents = (int) round((float) $wallet->balance * 100);
            $afterCents = $beforeCents + $amountCents;
            $amount = $amountCents / 100;
            $before = $beforeCents / 100;
            $after = $afterCents / 100;

            $wallet->update([
                'balance' => $after,
                'lifetime_credited' => ((int) round((float) $wallet->lifetime_credited * 100) + $amountCents) / 100,
            ]);

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'recharge',
                'direction' => 'credit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'status' => 'completed',
                'idempotency_key' => 'recharge-approved-' . $lockedRecharge->id,
                'reference' => $lockedRecharge->payment_reference
                    ?? 'RECHARGE-' . $lockedRecharge->id,
                'description' => 'Admin-approved wallet recharge',
                'metadata' => [
                    'recharge_request_id' => $lockedRecharge->id,
                    'approved_by' => $request->user()->id,
                ],
            ]);

            $lockedRecharge->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'wallet_transaction_id' => $transaction->id,
            ]);

            return $transaction->id;
        }, 3);

        return redirect()
            ->route('admin.recharges.show', $recharge)
            ->with('success', 'Recharge approved and wallet credited.');
    }

    public function reject(Request $request, RechargeRequest $recharge)
    {
        $data = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $recharge, $data) {
            $lockedRecharge = RechargeRequest::whereKey($recharge->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRecharge->status !== 'pending') {
                abort(409, 'This recharge request has already been processed.');
            }

            $lockedRecharge->update([
                'status' => 'rejected',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'admin_note' => $data['admin_note'],
            ]);
        }, 3);

        return redirect()
            ->route('admin.recharges.show', $recharge)
            ->with('success', 'Recharge request rejected.');
    }

    public function proof(RechargeRequest $recharge): BinaryFileResponse
    {
        abort_if(blank($recharge->proof_path), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($recharge->proof_path), 404);

        $root = realpath($disk->path(''));
        $path = realpath($disk->path($recharge->proof_path));

        abort_if(
            $root === false
                || $path === false
                || ! is_file($path)
                || ! str_starts_with($path, $root.DIRECTORY_SEPARATOR),
            404
        );

        $mimeType = $disk->mimeType($recharge->proof_path);
        abort_unless(in_array($mimeType, [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true), 404);

        return response()->download(
            $path,
            basename($recharge->proof_path),
            ['X-Content-Type-Options' => 'nosniff']
        );
    }
}