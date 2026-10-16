
@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-1">Admin Dashboard</h3>
            <p class="text-muted mb-0">Application overview and financial activity</p>
        </div>
        <div class="text-muted">
            {{ now()->format('d M Y') }}
        </div>
    </div>

    {{-- User and subscription cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Users</div>
                    <h3 class="fw-bold mt-2 mb-1">{{ number_format($totalUsers) }}</h3>
                    <span class="text-success small">
                        {{ number_format($activeUsers) }} active
                    </span>
                    <div class="mt-3">
                        <a href="{{ route('admin.users.index') }}"
                           class="small text-decoration-none">View users →</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Active Subscriptions</div>
                    <h3 class="fw-bold mt-2 mb-1">
                        {{ number_format($activeSubscriptions) }}
                    </h3>
                    <div class="mt-3">
                        <a href="{{ route('admin.subscriptions.index') }}"
                           class="small text-decoration-none">View subscriptions →</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Active Plans</div>
                    <h3 class="fw-bold mt-2 mb-1">{{ number_format($totalPlans) }}</h3>
                    <div class="mt-3">
                        <a href="{{ route('admin.subscription-plans.index') }}"
                           class="small text-decoration-none">Manage plans →</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Today's Transactions</div>
                    <h3 class="fw-bold mt-2 mb-1">
                        {{ number_format($todayTransactions) }}
                    </h3>
                    <span class="text-muted small">Completed entries</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial cards --}}
    <h5 class="fw-bold mb-3">Financial Overview</h5>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">All-time Credits</div>
                    <h4 class="fw-bold text-success mt-2">
                        ₹{{ number_format((float) $completedCredits, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">All-time Debits</div>
                    <h4 class="fw-bold text-danger mt-2">
                        ₹{{ number_format((float) $completedDebits, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Today's Credits</div>
                    <h4 class="fw-bold text-success mt-2">
                        ₹{{ number_format((float) $todayCredits, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Today's Debits</div>
                    <h4 class="fw-bold text-danger mt-2">
                        ₹{{ number_format((float) $todayDebits, 2) }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending requests --}}
    <h5 class="fw-bold mb-3">Pending Actions</h5>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <a href="{{ route('admin.recharges.index', ['status' => 'pending']) }}"
               class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Pending Recharges</div>
                            <h3 class="fw-bold text-warning mb-0">
                                {{ number_format($pendingRecharges) }}
                            </h3>
                        </div>
                        <span class="btn btn-outline-warning btn-sm">Review</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-4">
            <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}"
               class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Pending Withdrawals</div>
                            <h3 class="fw-bold text-danger mb-0">
                                {{ number_format($pendingWithdrawals) }}
                            </h3>
                        </div>
                        <span class="btn btn-outline-danger btn-sm">Review</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-4">
            <a href="{{ route('admin.daily-profits.index', ['status' => 'pending']) }}"
               class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Pending Daily Profits</div>
                            <h3 class="fw-bold text-primary mb-0">
                                {{ number_format($pendingProfits) }}
                            </h3>
                        </div>
                        <span class="btn btn-outline-primary btn-sm">Review</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Recent transactions --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Recent Wallet Transactions</h5>
            <a href="{{ route('admin.wallet-transactions.index') }}"
               class="btn btn-sm btn-outline-primary">View all</a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Direction</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentTransactions as $transaction)
                            <tr>
                                <td>#{{ $transaction->id }}</td>
                                <td>
                                    @if ($transaction->wallet?->user)
                                        <div class="fw-semibold">
                                            {{ $transaction->wallet->user->name }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $transaction->wallet->user->email }}
                                        </small>
                                    @else
                                        <span class="text-muted">User unavailable</span>
                                    @endif
                                </td>
                                <td>
                                    {{ ucwords(str_replace('_', ' ', $transaction->type)) }}
                                </td>
                                <td>
                                    <span class="badge {{ $transaction->direction === 'credit' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($transaction->direction) }}
                                    </span>
                                </td>
                                <td>
                                    ₹{{ number_format((float) $transaction->amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $transaction->created_at?->format('d M, h:i A') }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.wallet-transactions.show', $transaction) }}"
                                       class="btn btn-sm btn-outline-secondary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No transactions available yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Transaction type breakdown --}}
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Completed Transactions by Type</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Count</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactionTypes as $item)
                                    <tr>
                                        <td>{{ ucwords(str_replace('_', ' ', $item->type)) }}</td>
                                        <td>{{ number_format($item->total_count) }}</td>
                                        <td>
                                            ₹{{ number_format((float) $item->total_amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            No completed transactions yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent users --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Recently Registered Users</h5>
                    <a href="{{ route('admin.users.index') }}"
                       class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="card-body">
                    @forelse ($recentUsers as $user)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                            <div>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                                <div class="small text-muted mt-1">
                                    {{ $user->created_at?->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No users registered yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
