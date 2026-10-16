
@extends('admin.layouts.app')

@section('content')
@php
    $transaction = $walletTransaction;
    $user = $transaction->wallet?->user;
@endphp

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Transaction #{{ $transaction->id }}</h3>
            <p class="text-muted mb-0">Transaction details and audit information</p>
        </div>

        <a href="{{ route('admin.wallet-transactions.index') }}"
           class="btn btn-outline-secondary">
            Back to Transactions
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-muted">Amount</div>
                    <h3 class="{{ $transaction->direction === 'credit' ? 'text-success' : 'text-danger' }}">
                        {{ $transaction->direction === 'credit' ? '+' : '-' }}₹{{ number_format((float) $transaction->amount, 2) }}
                    </h3>
                </div>

                <div class="col-md-4">
                    <div class="text-muted">Direction</div>
                    <h5>{{ ucfirst($transaction->direction) }}</h5>
                </div>

                <div class="col-md-4">
                    <div class="text-muted">Status</div>
                    <h5>{{ ucfirst($transaction->status) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Transaction Information</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <strong>Transaction ID</strong>
                    <div>{{ $transaction->id }}</div>
                </div>

                <div class="col-md-6">
                    <strong>Type</strong>
                    <div>{{ ucwords(str_replace('_', ' ', $transaction->type)) }}</div>
                </div>

                <div class="col-md-6">
                    <strong>Reference</strong>
                    <div>{{ $transaction->reference ?: '—' }}</div>
                </div>

                <div class="col-md-6">
                    <strong>Idempotency Key</strong>
                    <div>{{ $transaction->idempotency_key ?: '—' }}</div>
                </div>

                <div class="col-md-6">
                    <strong>Balance Before</strong>
                    <div>₹{{ number_format((float) $transaction->balance_before, 2) }}</div>
                </div>

                <div class="col-md-6">
                    <strong>Balance After</strong>
                    <div>₹{{ number_format((float) $transaction->balance_after, 2) }}</div>
                </div>

                <div class="col-md-6">
                    <strong>Created At</strong>
                    <div>{{ $transaction->created_at?->format('d M Y, h:i:s A') }}</div>
                </div>

                <div class="col-md-6">
                    <strong>Updated At</strong>
                    <div>{{ $transaction->updated_at?->format('d M Y, h:i:s A') }}</div>
                </div>

                <div class="col-12">
                    <strong>Description</strong>
                    <div>{{ $transaction->description ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">User Information</h5>
        </div>

        <div class="card-body">
            @if ($user)
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Name</strong>
                        <div>{{ $user->name }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>Email</strong>
                        <div>{{ $user->email }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>User ID</strong>
                        <div>{{ $user->id }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>Wallet ID</strong>
                        <div>{{ $transaction->wallet_id }}</div>
                    </div>
                </div>
            @else
                <p class="text-muted mb-0">User information is unavailable.</p>
            @endif
        </div>
    </div>

    @if (!empty($transaction->metadata))
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0">Metadata</h5>
            </div>
            <div class="card-body">
                <pre class="mb-0 text-wrap">{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>
    @endif
</div>
@endsection
