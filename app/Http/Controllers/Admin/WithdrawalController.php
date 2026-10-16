<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $query = Withdrawal::with('user');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($request->status, ['pending', 'approved', 'rejected', 'paid'], true)) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->latest()->paginate(15)->withQueryString();

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load(['user', 'reviewer', 'walletTransaction']);

        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Approving reserves the requested amount by debiting the wallet.
     * The withdrawal request must not already have reserved/debited funds.
     */
    public function approve(Withdrawal $withdrawal)
    {
        DB::transaction(function () use ($withdrawal) {
            $item = Withdrawal::whereKey($withdrawal->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($item->status !== 'pending') {
                throw ValidationException::withMessages([
                    'withdrawal' => 'Only pending withdrawals can be approved.',
                ]);
            }

            $user = User::whereKey($item->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($user->status !== 'active') {
                throw ValidationException::withMessages([
                    'withdrawal' => 'Cannot approve withdrawal for an inactive user.',
                ]);
            }

            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                throw ValidationException::withMessages([
                    'withdrawal' => 'User wallet not found.',
                ]);
            }

            $amountCents = (int) round((float) $item->amount * 100);
            $feeCents = (int) round((float) $item->fee * 100);
            $netCents = (int) round((float) $item->net_amount * 100);
            $amount = $amountCents / 100;
            $fee = $feeCents / 100;
            $net = $netCents / 100;

            if ($amountCents <= 0 || $feeCents < 0 || $feeCents > $amountCents ||
                ($amountCents - $feeCents) !== $netCents) {
                throw ValidationException::withMessages([
                    'withdrawal' => 'Withdrawal amounts are inconsistent. Review the request.',
                ]);
            }

            $beforeCents = (int) round((float) $wallet->balance * 100);
            $before = $beforeCents / 100;

            if ($beforeCents < $amountCents) {
                throw ValidationException::withMessages([
                    'withdrawal' => 'Insufficient wallet balance to reserve this withdrawal.',
                ]);
            }

            $after = ($beforeCents - $amountCents) / 100;

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'withdrawal',
                'direction' => 'debit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'status' => 'completed',
                'idempotency_key' => 'withdrawal-approved-' . $item->id,
                'reference' => 'WITHDRAWAL-' . $item->id,
                'description' => 'Withdrawal amount reserved for request #' . $item->id,
                'metadata' => [
                    'withdrawal_id' => $item->id,
                    'approved_by' => auth()->id(),
                    'fee' => $fee,
                    'net_amount' => $net,
                ],
            ]);

            $wallet->balance = $after;
            $wallet->lifetime_debited = (
                (int) round((float) $wallet->lifetime_debited * 100) + $amountCents
            ) / 100;
            $wallet->save();

            $item->status = 'approved';
            $item->reviewed_by = auth()->id();
            $item->reviewed_at = now();
            $item->wallet_transaction_id = $transaction->id;
            $item->save();
        }, 3);

        return redirect()
            ->route('admin.withdrawals.show', $withdrawal)
            ->with('success', 'Withdrawal approved and amount reserved.');
    }

    public function reject(Request $request, Withdrawal $withdrawal)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($withdrawal, $validated) {
            $item = Withdrawal::whereKey($withdrawal->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($item->status !== 'pending') {
                throw ValidationException::withMessages([
                    'withdrawal' => 'Only pending withdrawals can be rejected.',
                ]);
            }

            $item->status = 'rejected';
            $item->reviewed_by = auth()->id();
            $item->reviewed_at = now();
            $item->rejection_reason = $validated['rejection_reason'];
            $item->save();
        });

        return redirect()
            ->route('admin.withdrawals.show', $withdrawal)
            ->with('success', 'Withdrawal request rejected.');
    }

    public function markPaid(Request $request, Withdrawal $withdrawal)
    {
        $validated = $request->validate([
            'payment_reference' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($withdrawal, $validated) {
            $item = Withdrawal::whereKey($withdrawal->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($item->status !== 'approved') {
                throw ValidationException::withMessages([
                    'withdrawal' => 'Only approved withdrawals can be marked as paid.',
                ]);
            }

            if (!$item->wallet_transaction_id) {
                throw ValidationException::withMessages([
                    'withdrawal' => 'Wallet reservation transaction is missing.',
                ]);
            }

            $item->status = 'paid';
            $item->paid_at = now();
            $item->payment_reference = $validated['payment_reference'];
            $item->save();
        });

        return redirect()
            ->route('admin.withdrawals.show', $withdrawal)
            ->with('success', 'Withdrawal marked as paid.');
    }
}