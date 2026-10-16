
@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daily Profit #{{ $dailyProfit->id }}</h2>
        <a href="{{ route('admin.daily-profits.index') }}"
           class="btn btn-outline-secondary">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Entry Details</div>
        <div class="card-body">
            <p><strong>User:</strong> {{ $dailyProfit->user?->name ?? 'Deleted user' }}</p>
            <p><strong>Email:</strong> {{ $dailyProfit->user?->email ?? '—' }}</p>
            <p><strong>Plan:</strong> {{ $dailyProfit->subscription?->plan?->name ?? 'Plan unavailable' }}</p>
            <p><strong>Profit date:</strong> {{ $dailyProfit->profit_date?->format('d M Y') }}</p>
            <p><strong>Amount:</strong> ₹{{ number_format((float) $dailyProfit->amount, 2) }}</p>
            <p><strong>Status:</strong> {{ ucfirst($dailyProfit->status) }}</p>
            <p><strong>Wallet transaction:</strong> {{ $dailyProfit->wallet_transaction_id ?? 'Not credited' }}</p>
            <p><strong>Admin note:</strong> {{ $dailyProfit->admin_note ?: '—' }}</p>
        </div>
    </div>

    @if($dailyProfit->status === 'pending')
        <div class="card">
            <div class="card-header">Review Entry</div>
            <div class="card-body">
                <form method="POST"
                      action="{{ route('admin.daily-profits.approve', $dailyProfit) }}"
                      onsubmit="return confirm('Approve this entry and credit the amount to the user wallet?');">
                    @csrf
                    <button class="btn btn-success mb-3" type="submit">
                        Approve & Credit Wallet
                    </button>
                </form>

                <form method="POST"
                      action="{{ route('admin.daily-profits.reject', $dailyProfit) }}">
                    @csrf

                    <label for="admin_note" class="form-label">
                        Rejection reason
                    </label>
                    <textarea id="admin_note" name="admin_note"
                              class="form-control mb-2" rows="3"
                              maxlength="1000" required>{{ old('admin_note') }}</textarea>

                    <button class="btn btn-danger" type="submit"
                            onclick="return confirm('Reject this pending entry?');">
                        Reject Entry
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
