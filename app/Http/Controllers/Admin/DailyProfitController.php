<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\DailyProfit;
use App\Models\UserSubscription;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DailyProfitController extends Controller
{
    public function index(Request $request)
    {
        $query = DailyProfit::with(['user', 'subscription.plan']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') &&
            in_array($request->status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('profit_date', $request->date);
        }

        $profits = $query->latest('profit_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.daily-profits.index', compact('profits'));
    }

    public function show(DailyProfit $dailyProfit)
    {
        $dailyProfit->load(['user', 'subscription.plan', 'walletTransaction']);

        return view('admin.daily-profits.show', compact('dailyProfit'));
    }

    public function approve(DailyProfit $dailyProfit)
    {
        DB::transaction(function () use ($dailyProfit) {
            $profit = DailyProfit::whereKey($dailyProfit->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($profit->status !== 'pending') {
                throw ValidationException::withMessages([
                    'profit' => 'Only pending entries can be approved.',
                ]);
            }

            $amountCents = (int) round((float) $profit->amount * 100);
            if ($amountCents <= 0) {
                throw ValidationException::withMessages([
                    'profit' => 'Profit amount must be greater than zero.',
                ]);
            }

            $subscription = UserSubscription::whereKey($profit->user_subscription_id)
                ->lockForUpdate()
                ->firstOrFail();
            $profitDate = $profit->profit_date->toDateString();
            $firstEligibleDate = $subscription->starts_at?->toDateString();
            $lastEligibleDate = $subscription->ends_at?->copy()->subDay()->toDateString();
            $expectedAmountCents = (int) round((float) $subscription->daily_income * 100);

            if (
                $subscription->status === 'cancelled'
                || ! $firstEligibleDate
                || ! $lastEligibleDate
                || $profitDate < $firstEligibleDate
                || $profitDate > $lastEligibleDate
                || $amountCents !== $expectedAmountCents
                || $profit->profit_date->greaterThanOrEqualTo(today())
            ) {
                throw ValidationException::withMessages([
                    'profit' => 'This profit entry does not match an eligible subscription day and amount.',
                ]);
            }

            // Lock the user before creating/locking their wallet.
            $user = \App\Models\User::whereKey($profit->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($user->status !== 'active') {
                throw ValidationException::withMessages([
                    'profit' => 'Cannot credit a suspended user.',
                ]);
            }

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'lifetime_credited' => 0, 'lifetime_debited' => 0]
            );

            $wallet = Wallet::whereKey($wallet->id)
                ->lockForUpdate()
                ->firstOrFail();

            $beforeCents = (int) round((float) $wallet->balance * 100);
            $amount = $amountCents / 100;
            $before = $beforeCents / 100;
            $after = ($beforeCents + $amountCents) / 100;

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'daily_profit',
                'direction' => 'credit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'status' => 'completed',
                'idempotency_key' => 'daily-profit-approved-' . $profit->id,
                'reference' => 'PROFIT-' . $profit->id,
                'description' => 'Approved daily profit entry #' . $profit->id,
                'metadata' => [
                    'daily_profit_id' => $profit->id,
                    'approved_by' => auth()->id(),
                ],
            ]);

            $wallet->balance = $after;
            $wallet->lifetime_credited = (
                (int) round((float) $wallet->lifetime_credited * 100) + $amountCents
            ) / 100;
            $wallet->save();

            $profit->status = 'approved';
            $profit->wallet_transaction_id = $transaction->id;
            $profit->save();
        }, 3);

        return redirect()
            ->route('admin.daily-profits.show', $dailyProfit)
            ->with('success', 'Daily profit approved and credited.');
    }

    public function reject(Request $request, DailyProfit $dailyProfit)
    {
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        $profitId = $dailyProfit->id;

        DB::transaction(function () use ($profitId, $validated) {
            $profit = DailyProfit::whereKey($profitId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($profit->status !== 'pending') {
                throw ValidationException::withMessages([
                    'profit' => 'Only pending entries can be rejected.',
                ]);
            }

            // Add admin_note column to daily_profits before using this.
            $profit->status = 'rejected';
            $profit->admin_note = $validated['admin_note'];
            $profit->save();
        });

        return redirect()
            ->route('admin.daily-profits.show', $dailyProfit)
            ->with('success', 'Daily profit entry rejected.');
    }
}