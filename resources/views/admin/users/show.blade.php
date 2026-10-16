
@extends('admin.layouts.app')

@section('title', 'User Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>User Details</h3>
    <a href="{{ route('admin.users.index') }}"
       class="btn btn-outline-secondary">Back to Users</a>
</div>

<div class="admin-card mb-4">
    <h5>Account Information</h5>
    <hr>

    <p><strong>ID:</strong> {{ $user->id }}</p>
    <p><strong>Name:</strong> {{ $user->name }}</p>
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>Role:</strong> {{ $user->is_admin ? 'Admin' : 'User' }}</p>
    <p><strong>Status:</strong> {{ ucfirst($user->status) }}</p>
    <p>
        <strong>Wallet Balance:</strong>
        ₹{{ number_format((float) ($user->wallet?->balance ?? 0), 2) }}
    </p>

    <a href="{{ route('admin.users.edit', $user) }}"
       class="btn btn-primary">Edit User</a>
</div>

<div class="admin-card mb-4">
    <h5>User Subscriptions</h5>
    <hr>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Purchase Price</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($user->subscriptions as $subscription)
                    <tr>
                        <td>{{ $subscription->plan?->name ?? 'Plan unavailable' }}</td>
                        <td>₹{{ number_format((float) $subscription->purchase_price, 2) }}</td>
                        <td>{{ ucfirst($subscription->status) }}</td>
                        <td>{{ $subscription->starts_at?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $subscription->ends_at?->format('d M Y') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No subscriptions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="admin-card">
    <h5>Recent Wallet Transactions</h5>
    <hr>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Direction</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($user->wallet?->transactions ?? [] as $transaction)
                    <tr>
                        <td>{{ $transaction->created_at?->format('d M Y H:i') ?? '—' }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</td>
                        <td>{{ ucfirst($transaction->direction) }}</td>
                        <td>₹{{ number_format((float) $transaction->amount, 2) }}</td>
                        <td>{{ ucfirst($transaction->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
