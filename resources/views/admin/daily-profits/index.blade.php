
@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">Daily Profit Management</h2>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control"
                   value="{{ request('search') }}"
                   placeholder="Search user name or email">
        </div>

        <div class="col-md-3">
            <input type="date" name="date" class="form-control"
                   value="{{ request('date') }}">
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(['pending', 'approved', 'rejected'] as $status)
                    <option value="{{ $status }}"
                        @selected(request('status') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.daily-profits.index') }}"
               class="btn btn-outline-secondary mt-1">Reset</a>
        </div>
    </form>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profits as $profit)
                        <tr>
                            <td>{{ $profit->id }}</td>
                            <td>
                                {{ $profit->user?->name ?? 'Deleted user' }}
                                <br>
                                <small>{{ $profit->user?->email }}</small>
                            </td>
                            <td>
                                {{ $profit->subscription?->plan?->name ?? 'Plan unavailable' }}
                            </td>
                            <td>{{ $profit->profit_date?->format('d M Y') }}</td>
                            <td>₹{{ number_format((float) $profit->amount, 2) }}</td>
                            <td>
                                @php
                                    $badge = match($profit->status) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'warning',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">
                                    {{ ucfirst($profit->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.daily-profits.show', $profit) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                No daily profit entries found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $profits->links() }}</div>
</div>
@endsection
