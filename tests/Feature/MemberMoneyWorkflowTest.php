<?php

namespace Tests\Feature;

use App\Models\DailyProfit;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MemberMoneyWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->forceFill(['is_admin' => false, 'status' => 'active'])->save();

        return $user;
    }

    public function test_registration_creates_member_and_wallet_and_member_pages_are_separate_from_admin(): void
    {
        $this->get(route('plans.index'))
            ->assertRedirect(route('login'));

        $this->post(route('register.store'), [
            'name' => 'Workflow Member',
            'email' => 'workflow-member@example.test',
            'password' => 'Member@12345',
            'password_confirmation' => 'Member@12345',
        ])->assertRedirect(route('plans.index'));

        $user = User::where('email', 'workflow-member@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => '0.00',
        ]);
        $this->assertFalse($user->fresh()->is_admin);
        $this->assertSame('active', $user->fresh()->status);
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true, 'status' => 'active'])->save();
        $this->actingAs($user)
            ->get(route('plans.index'))
            ->assertOk();
        $this->get(route('admin.withdrawals.index'))->assertForbidden();

        $this->actingAs($admin)
            ->get(route('plans.index'))
            ->assertForbidden();
    }

    public function test_plan_purchase_debits_wallet_once_and_creates_active_subscription(): void
    {
        $user = $this->makeUser();
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 50.00,
            'lifetime_credited' => 50.00,
            'lifetime_debited' => 0,
        ]);
        $plan = SubscriptionPlan::create([
            'name' => 'Test Plan',
            'slug' => 'test-plan-'.uniqid(),
            'price' => 25.50,
            'daily_income' => 2.00,
            'duration_days' => 5,
            'total_income' => 10.00,
            'is_vip' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('plans.purchase', $plan))
            ->assertRedirect(route('subscriptions.mine'))
            ->assertSessionHas('success');

        $subscription = UserSubscription::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('active', $subscription->status);
        $this->assertSame(5, $subscription->duration_days);
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => '24.50',
            'lifetime_debited' => '25.50',
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'subscription_purchase',
            'direction' => 'debit',
            'amount' => '25.50',
            'status' => 'completed',
        ]);

        $this->actingAs($user)
            ->post(route('plans.purchase', $plan))
            ->assertSessionHasErrors('plan');
        $this->assertSame(1, UserSubscription::where('user_id', $user->id)->count());
    }

    public function test_plan_purchase_does_not_spend_pending_withdrawal_amount(): void
    {
        $user = $this->makeUser();
        Wallet::create([
            'user_id' => $user->id,
            'balance' => 100,
            'lifetime_credited' => 100,
            'lifetime_debited' => 0,
        ]);
        $plan = SubscriptionPlan::create([
            'name' => 'Reserved Balance Plan',
            'slug' => 'reserved-balance-'.uniqid(),
            'price' => 50,
            'daily_income' => 1,
            'duration_days' => 5,
            'total_income' => 5,
            'is_vip' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        Withdrawal::create([
            'user_id' => $user->id,
            'amount' => 60,
            'fee' => 0,
            'net_amount' => 60,
            'payment_method' => 'upi',
            'payment_details' => ['upi_id' => 'member@bank'],
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('plans.purchase', $plan))
            ->assertSessionHasErrors('plan');

        $this->assertSame(0, UserSubscription::where('user_id', $user->id)->count());
    }

    public function test_daily_profit_generator_is_catchup_safe_and_does_not_duplicate_dates(): void
    {
        $user = $this->makeUser();
        $plan = SubscriptionPlan::create([
            'name' => 'Profit Plan',
            'slug' => 'profit-plan-'.uniqid(),
            'price' => 20,
            'daily_income' => 1.25,
            'duration_days' => 5,
            'total_income' => 6.25,
            'is_vip' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $start = now()->subDays(2)->startOfDay();
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'purchase_price' => 20,
            'daily_income' => 1.25,
            'total_income' => 6.25,
            'duration_days' => 5,
            'starts_at' => $start,
            'ends_at' => $start->copy()->addDays(5),
            'status' => 'active',
        ]);

        $this->artisan('profits:generate')
            ->assertSuccessful();
        $this->assertSame(2, DailyProfit::where('user_subscription_id', $subscription->id)->count());

        $this->artisan('profits:generate', ['--date' => now()->toDateString()])
            ->assertSuccessful();
        $this->artisan('profits:generate', ['--date' => now()->toDateString()])
            ->assertSuccessful();

        $this->assertSame(3, DailyProfit::where('user_subscription_id', $subscription->id)->count());
        $this->assertDatabaseHas('daily_profits', [
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => '1.25',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('profits.mine'))
            ->assertOk()
            ->assertSee('Profit Plan');
    }

    public function test_admin_daily_profit_approval_credits_wallet_exactly_once(): void
    {
        $user = $this->makeUser();
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true, 'status' => 'active'])->save();
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'lifetime_credited' => 0,
            'lifetime_debited' => 0,
        ]);
        $plan = SubscriptionPlan::create([
            'name' => 'Approval Plan',
            'slug' => 'approval-plan-'.uniqid(),
            'price' => 20,
            'daily_income' => 1.27,
            'duration_days' => 5,
            'total_income' => 6.35,
            'is_vip' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'purchase_price' => 20,
            'daily_income' => 1.27,
            'total_income' => 6.35,
            'duration_days' => 5,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(4),
            'status' => 'active',
        ]);
        $profit = DailyProfit::create([
            'user_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'profit_date' => now()->subDay()->toDateString(),
            'amount' => 1.27,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.daily-profits.approve', $profit))
            ->assertRedirect(route('admin.daily-profits.show', $profit));

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => '1.27',
            'lifetime_credited' => '1.27',
        ]);
        $this->assertDatabaseHas('daily_profits', [
            'id' => $profit->id,
            'status' => 'approved',
        ]);
        $this->assertSame(1, WalletTransaction::where('idempotency_key', 'daily-profit-approved-'.$profit->id)->count());

        $this->actingAs($admin)
            ->post(route('admin.daily-profits.approve', $profit))
            ->assertSessionHasErrors('profit');

        $this->assertSame(1, WalletTransaction::where('idempotency_key', 'daily-profit-approved-'.$profit->id)->count());
    }

    public function test_withdrawal_request_validates_pending_total_and_admin_settlement_flow(): void
    {
        $user = $this->makeUser();
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true, 'status' => 'active'])->save();
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 100.00,
            'lifetime_credited' => 100.00,
            'lifetime_debited' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('withdrawals.store'), [
                'amount' => '60.25',
                'payment_method' => 'upi',
                'upi_id' => 'member@bank',
            ])
            ->assertRedirect(route('withdrawals.mine'));

        $withdrawal = $user->withdrawals()->latest()->firstOrFail();
        $this->assertSame('pending', $withdrawal->status);
        $this->assertSame('member@bank', $withdrawal->payment_details['upi_id']);

        $this->actingAs($user)
            ->post(route('withdrawals.store'), [
                'amount' => '40.00',
                'payment_method' => 'upi',
                'upi_id' => 'member@bank',
            ])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => '100.00',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal))
            ->assertRedirect(route('admin.withdrawals.show', $withdrawal));
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => '39.75',
            'lifetime_debited' => '60.25',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.mark-paid', $withdrawal), [
                'payment_reference' => 'BANK-SETTLED-001',
            ])
            ->assertRedirect(route('admin.withdrawals.show', $withdrawal));
        $this->assertDatabaseHas('withdrawals', [
            'id' => $withdrawal->id,
            'status' => 'paid',
            'payment_reference' => 'BANK-SETTLED-001',
        ]);
        $this->assertSame(1, WalletTransaction::where('idempotency_key', 'withdrawal-approved-'.$withdrawal->id)->count());
    }
}
