<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WithdrawalRequestController extends Controller
{
    public function index(Request $request): View
    {
        $wallet = Wallet::where('user_id', $request->user()->id)->first();
        $withdrawals = Withdrawal::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('finance.withdrawals', compact('wallet', 'withdrawals'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999999.99'],
            'payment_method' => ['required', 'in:upi,bank_transfer'],
            'upi_id' => ['required_if:payment_method,upi', 'nullable', 'string', 'max:255'],
            'account_name' => ['required_if:payment_method,bank_transfer', 'nullable', 'string', 'max:255'],
            'account_number' => ['required_if:payment_method,bank_transfer', 'nullable', 'string', 'max:34'],
            'ifsc' => ['required_if:payment_method,bank_transfer', 'nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($request, $data): void {
            $user = User::whereKey($request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($user->status !== 'active' || $user->is_admin) {
                throw ValidationException::withMessages([
                    'amount' => 'This account cannot submit a withdrawal request.',
                ]);
            }

            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                throw ValidationException::withMessages([
                    'amount' => 'Wallet not found.',
                ]);
            }

            $amountCents = (int) round((float) $data['amount'] * 100);
            $balanceCents = (int) round((float) $wallet->balance * 100);
            $pendingCents = (int) round(
                (float) Withdrawal::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->sum('amount') * 100
            );

            if ($amountCents <= 0 || $amountCents + $pendingCents > $balanceCents) {
                throw ValidationException::withMessages([
                    'amount' => 'The withdrawal amount exceeds your available balance after pending requests.',
                ]);
            }

            $paymentDetails = $data['payment_method'] === 'upi'
                ? ['upi_id' => trim($data['upi_id'])]
                : [
                    'account_name' => trim($data['account_name']),
                    'account_number' => trim($data['account_number']),
                    'ifsc' => strtoupper(trim($data['ifsc'])),
                ];

            Withdrawal::create([
                'user_id' => $user->id,
                'amount' => $amountCents / 100,
                'fee' => 0,
                'net_amount' => $amountCents / 100,
                'payment_method' => $data['payment_method'],
                'payment_details' => $paymentDetails,
                'status' => 'pending',
            ]);
        }, 3);

        return redirect()->route('withdrawals.mine')
            ->with('success', 'Withdrawal request submitted for admin review. No funds are deducted until approval.');
    }
}
