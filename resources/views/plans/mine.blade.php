```blade
@extends('layouts.user')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">My Subscriptions</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Purchase Price</th>
                        <th>Duration</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td>{{ $subscription->plan?->name ?? 'Plan unavailable' }}</td>
                            <td>₹{{ number_format((float) $subscription->purchase_price, 2) }}</td>
                            <td>{{ $subscription->duration_days }} days</td>
                            <td>{{ $subscription->starts_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td>{{ $subscription->ends_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                You have not purchased any plans yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $subscriptions->links() }}
    </div>

    <a href="{{ route('plans.index') }}" class="btn btn-primary mt-2">
        Browse Plans
    </a>
</div>
@endsection
```