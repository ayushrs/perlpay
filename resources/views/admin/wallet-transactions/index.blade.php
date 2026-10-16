
@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Wallet Transactions</h3>
            <p class="text-muted mb-0">Read-only financial ledger</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            Please check the filters and try again.
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Completed Credits</div>
                    <h4 class="text-success mb-0">
                        ₹{{ number_format((float) $totalCredit, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Completed Debits</div>
                    <h4 class="text-danger mb-0">
                        ₹{{ number_format((float) $totalDebit, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Completed Transactions</div>
                    <h4 class="mb-0">{{ number_format($completedCount) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET"
                  action="{{ route('admin.wallet-transactions.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text"
                               name="search"
                               class="form-control"
                               value="{{ $filters['search'] ?? '' }}"
                               placeholder="Name, email or reference">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All types</option>
                            @foreach ([
                                'recharge' => 'Recharge',
                                'subscription_purchase' => 'Subscription Purchase',
                                'daily_profit' => 'Daily Profit',
                                'withdrawal' => 'Withdrawal',
                            ] as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(($filters['type'] ?? '') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Direction</label>
                        <select name="direction" class="form-select">
                            <option value="">All</option>
                            <option value="credit"
                                @selected(($filters['direction'] ?? '') === 'credit')>
                                Credit
                            </option>
                            <option value="debit"
                                @selected(($filters['direction'] ?? '') === 'debit')>
                                Debit
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All statuses</option>
                            @foreach (['pending', 'completed', 'failed', 'rejected'] as $status)
                                <option value="{{ $status }}"
                                    @selected(($filters['status'] ?? '') === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">From date</label>
                        <input type="date"
                               name="from"
                               class="form-control"
                               value="{{ $filters['from'] ?? '' }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">To date</label>
                        <input type="date"
                               name="to"
                               class="form-control"
                               value="{{ $filters['to'] ?? '' }}">
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary" type="submit">
                            Apply Filters
                        </button>
                        <a href="{{ route('admin.wallet-transactions.index') }}"
                           class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Direction</th>
                            <th>Amount</th>
                            <th>Balance Before</th>
                            <th>Balance After</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->id }}</td>
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
                                <td class="fw-semibold">
                                    ₹{{ number_format((float) $transaction->amount, 2) }}
                                </td>
                                <td>
                                    ₹{{ number_format((float) $transaction->balance_before, 2) }}
                                </td>
                                <td>
                                    ₹{{ number_format((float) $transaction->balance_after, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $transaction->created_at?->format('d M Y, h:i A') }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.wallet-transactions.show', $transaction) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    No transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
