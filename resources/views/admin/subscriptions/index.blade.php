```blade
@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">User Subscriptions</h2>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control"
                   value="{{ request('search') }}"
                   placeholder="Search user name or email">
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(['pending', 'active', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}"
                        @selected(request('status') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <button class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.subscriptions.index') }}"
               class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Purchase Price</th>
                        <th>Duration</th>
                        <th>Start Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td>{{ $subscription->id }}</td>
                            <td>
                                {{ $subscription->user?->name ?? 'Deleted user' }}
                                <br>
                                <small>{{ $subscription->user?->email }}</small>
                            </td>
                            <td>{{ $subscription->plan?->name ?? 'Plan unavailable' }}</td>
                            <td>₹{{ number_format((float) $subscription->purchase_price, 2) }}</td>
                            <td>{{ $subscription->duration_days }} days</td>
                            <td>{{ $subscription->starts_at?->format('d M Y') ?? '—' }}</td>
                            <td>{{ ucfirst($subscription->status) }}</td>
                            <td>
                                <a href="{{ route('admin.subscriptions.show', $subscription) }}"
                                   class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                No subscriptions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $subscriptions->links() }}</div>
</div>
@endsection
