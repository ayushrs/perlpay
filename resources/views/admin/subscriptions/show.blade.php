```blade
@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Subscription #{{ $subscription->id }}</h2>
        <a href="{{ route('admin.subscriptions.index') }}"
           class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">Purchase Details</div>
        <div class="card-body">
            <p><strong>User:</strong> {{ $subscription->user?->name ?? 'Deleted user' }}</p>
            <p><strong>Email:</strong> {{ $subscription->user?->email ?? '—' }}</p>
            <p><strong>Plan:</strong> {{ $subscription->plan?->name ?? 'Plan unavailable' }}</p>
            <p><strong>Purchase price:</strong> ₹{{ number_format((float) $subscription->purchase_price, 2) }}</p>
            <p><strong>Recorded daily amount:</strong> ₹{{ number_format((float) $subscription->daily_income, 2) }}</p>
            <p><strong>Recorded total amount:</strong> ₹{{ number_format((float) $subscription->total_income, 2) }}</p>
            <p><strong>Duration:</strong> {{ $subscription->duration_days }} days</p>
            <p><strong>Starts:</strong> {{ $subscription->starts_at?->format('d M Y H:i') ?? '—' }}</p>
            <p><strong>Ends:</strong> {{ $subscription->ends_at?->format('d M Y H:i') ?? '—' }}</p>
            <p><strong>Status:</strong> {{ ucfirst($subscription->status) }}</p>
            <p class="mb-0"><strong>Purchased:</strong> {{ $subscription->created_at?->format('d M Y H:i') }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Recorded Daily Entries</div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscription->dailyProfits as $profit)
                        <tr>
                            <td>{{ $profit->profit_date?->format('d M Y') }}</td>
                            <td>₹{{ number_format((float) $profit->amount, 2) }}</td>
                            <td>{{ ucfirst($profit->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-3">
                                No daily entries recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
