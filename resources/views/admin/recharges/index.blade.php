
@extends('admin.layouts.app')

@section('title', 'Recharge Management')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3>Recharge Management</h3>
        <p class="text-muted mb-0">Review incoming wallet recharge requests.</p>
    </div>
</div>

<div class="admin-card">
    <form method="GET" action="{{ route('admin.recharges.index') }}"
          class="row g-2 mb-4">
        <div class="col-md-6">
            <input
                type="text"
                name="search"
                class="form-control"
                aria-label="Search recharge requests"
                placeholder="Search user, email or payment reference"
                value="{{ request('search') }}"
            >
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select" aria-label="Filter recharge requests by status">
                <option value="">All statuses</option>
                @foreach(['pending', 'approved', 'rejected'] as $status)
                    <option value="{{ $status }}"
                        @selected(request('status') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.recharges.index') }}"
               class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Payment Reference</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recharges as $recharge)
                    <tr>
                        <td>{{ $recharge->id }}</td>
                        <td>
                            {{ $recharge->user?->name ?? 'Unknown' }}
                            <div class="small text-muted">
                                {{ $recharge->user?->email }}
                            </div>
                        </td>
                        <td>
                            ₹{{ number_format((float) $recharge->amount, 2) }}
                        </td>
                        <td>{{ $recharge->payment_reference ?: '-' }}</td>
                        <td>{{ $recharge->created_at?->format('d M Y, h:i A') ?? '—' }}</td>
                        <td>
                            @php
                                $badge = match($recharge->status) {
                                    'approved' => 'text-bg-success',
                                    'rejected' => 'text-bg-danger',
                                    default => 'text-bg-warning',
                                };
                            @endphp

                            <span class="badge {{ $badge }}">
                                {{ ucfirst($recharge->status) }}
                            </span>
                        </td>
                        <td>
                            <a
                                href="{{ route('admin.recharges.show', $recharge) }}"
                                class="btn btn-sm btn-outline-primary"
                            >
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            No recharge requests found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $recharges->links() }}
</div>
@endsection
