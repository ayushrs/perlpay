<?php

namespace Tests\Feature;

use App\Models\RechargeRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminRechargeRequestTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill([
            'is_admin' => true,
            'status' => 'active',
        ])->save();

        return $admin;
    }

    private function requestFor(User $user, array $attributes = []): RechargeRequest
    {
        return RechargeRequest::create(array_merge([
            'user_id' => $user->id,
            'amount' => 25.50,
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'TEST-REF-001',
            'status' => 'pending',
        ], $attributes));
    }

    public function test_admin_can_list_and_view_recharge_requests(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $recharge = $this->requestFor($user, ['proof_path' => 'missing/proof.pdf']);

        $this->actingAs($admin)
            ->get(route('admin.recharges.index'))
            ->assertOk()
            ->assertSee('TEST-REF-001');

        $this->actingAs($admin)
            ->get(route('admin.recharges.show', $recharge))
            ->assertOk()
            ->assertSee('Approve & Credit Wallet', false)
            ->assertSee(route('admin.recharges.proof', $recharge), false);
    }

    public function test_approval_credits_wallet_once_and_records_transaction(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 10.25,
            'lifetime_credited' => 10.25,
            'lifetime_debited' => 0,
        ]);
        $recharge = $this->requestFor($user);

        $this->actingAs($admin)
            ->post(route('admin.recharges.approve', $recharge))
            ->assertRedirect(route('admin.recharges.show', $recharge))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => '35.75',
            'lifetime_credited' => '35.75',
        ]);
        $this->assertDatabaseHas('recharge_requests', [
            'id' => $recharge->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'recharge',
            'direction' => 'credit',
            'amount' => '25.50',
            'idempotency_key' => 'recharge-approved-'.$recharge->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.recharges.approve', $recharge))
            ->assertStatus(409);

        $this->assertSame(
            1,
            WalletTransaction::where('idempotency_key', 'recharge-approved-'.$recharge->id)->count()
        );

        $secondUser = User::factory()->create();
        $secondRecharge = $this->requestFor($secondUser, ['amount' => 0.10]);

        $this->actingAs($admin)
            ->post(route('admin.recharges.approve', $secondRecharge))
            ->assertRedirect(route('admin.recharges.show', $secondRecharge));

        $this->assertDatabaseHas('wallets', [
            'user_id' => $secondUser->id,
            'balance' => '0.10',
            'lifetime_credited' => '0.10',
        ]);
        $this->assertSame(
            1,
            WalletTransaction::where('idempotency_key', 'recharge-approved-'.$secondRecharge->id)->count()
        );
    }

    public function test_rejection_requires_a_reason_and_cannot_be_repeated(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $recharge = $this->requestFor($user);

        $this->actingAs($admin)
            ->from(route('admin.recharges.show', $recharge))
            ->post(route('admin.recharges.reject', $recharge), ['admin_note' => ''])
            ->assertRedirect(route('admin.recharges.show', $recharge))
            ->assertSessionHasErrors('admin_note');

        $this->actingAs($admin)
            ->post(route('admin.recharges.reject', $recharge), [
                'admin_note' => 'Payment reference could not be verified.',
            ])
            ->assertRedirect(route('admin.recharges.show', $recharge));

        $this->assertDatabaseHas('recharge_requests', [
            'id' => $recharge->id,
            'status' => 'rejected',
            'admin_note' => 'Payment reference could not be verified.',
            'reviewed_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.recharges.reject', $recharge), [
                'admin_note' => 'Second decision.',
            ])
            ->assertStatus(409);
    }

    public function test_payment_proof_is_served_only_from_the_private_disk(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        Storage::fake('local');
        Storage::fake('public');

        $proofPath = 'recharge-proofs/proof.png';
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jXioAAAAASUVORK5CYII=');
        Storage::disk('local')->put($proofPath, $png);
        $recharge = $this->requestFor($user, ['proof_path' => $proofPath]);

        $this->actingAs($admin)
            ->get(route('admin.recharges.proof', $recharge))
            ->assertDownload('proof.png');

        Storage::disk('local')->delete($proofPath);
        Storage::disk('public')->put($proofPath, $png);

        $this->actingAs($admin)
            ->get(route('admin.recharges.proof', $recharge))
            ->assertNotFound();
    }
}
