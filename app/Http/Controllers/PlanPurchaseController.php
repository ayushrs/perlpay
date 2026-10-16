<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlanPurchaseController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $wallet = Wallet::where('user_id', auth()->id())->first();

        return view('plans.index', compact('plans', 'wallet'));
    }

    public function purchase(Request $request, SubscriptionPlan $plan)
    {
        abort_unless($plan->is_active, 404);

        $userId = $request->user()->id;

        DB::transaction(function () use ($plan, $userId) {
            // Lock user and wallet rows to prevent concurrent spending.
            $user = User::whereKey($userId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($user->status !== 'active') {
                throw ValidationException::withMessages([
                    'plan' => 'Your account is not active.',
                ]);
            }

            $wallet = Wallet::where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                throw ValidationException::withMessages([
                    'plan' => 'Wallet not found. Please contact support.',
                ]);
            }

            // Refresh plan data inside the transaction.
            $currentPlan = SubscriptionPlan::whereKey($plan->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$currentPlan) {
                throw ValidationException::withMessages([
                    'plan' => 'This plan is no longer available.',
                ]);
            }

            $priceCents = (int) round((float) $currentPlan->price * 100);
            $balanceCents = (int) round((float) $wallet->balance * 100);
            $pendingWithdrawalCents = (int) round(
                (float) Withdrawal::where('user_id', $userId)
                    ->where('status', 'pending')
                    ->sum('amount') * 100
            );
            $availableCents = $balanceCents - $pendingWithdrawalCents;

            if ($priceCents <= 0 || $availableCents < $priceCents) {
                throw ValidationException::withMessages([
                    'plan' => 'Insufficient available balance after pending withdrawals. Please recharge first.',
                ]);
            }

            $price = $priceCents / 100;
            $before = $balanceCents / 100;
            $after = ($balanceCents - $priceCents) / 100;

            $startsAt = now();
            $endsAt = $startsAt->copy()
                ->addDays((int) $currentPlan->duration_days);

            $subscription = UserSubscription::create([
                'user_id' => $userId,
                'subscription_plan_id' => $currentPlan->id,
                'purchase_price' => $currentPlan->price,
                'daily_income' => $currentPlan->daily_income,
                'total_income' => $currentPlan->total_income,
                'duration_days' => $currentPlan->duration_days,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => 'active',
            ]);

            $wallet->balance = $after;
            $wallet->lifetime_debited = (
                (int) round((float) $wallet->lifetime_debited * 100) + $priceCents
            ) / 100;
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'subscription_purchase',
                'direction' => 'debit',
                'amount' => $price,
                'balance_before' => $before,
                'balance_after' => $after,
                'status' => 'completed',
                'idempotency_key' => 'subscription-purchase-' . Str::uuid(),
                'reference' => 'SUB-' . $subscription->id,
                'description' => 'Subscription plan purchase: '
                    . $currentPlan->name,
                'metadata' => [
                    'user_subscription_id' => $subscription->id,
                    'subscription_plan_id' => $currentPlan->id,
                ],
            ]);
        }, 3);

        return redirect()
            ->route('subscriptions.mine')
            ->with('success', 'Plan purchased successfully.');
    }

    public function mySubscriptions(Request $request)
    {
        $subscriptions = UserSubscription::with('plan')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('plans.mine', compact('subscriptions'));
    }
}