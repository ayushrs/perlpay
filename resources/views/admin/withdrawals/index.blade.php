
@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">Withdrawal Management</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-5">
            <input name="search" class="form-control"
                   value="{{ request('search') }}"
                   placeholder="Search user name or email">
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(['pending', 'approved', 'paid', 'rejected'] as $status)
                    <option value="{{ $status }}"
                        @selected(request('status') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <button class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.withdrawals.index') }}"
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
                        <th>Requested</th>
                        <th>Fee</th>
                        <th>Net Payable</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $withdrawal)
                        <tr>
                            <td>{{ $withdrawal->id }}</td>
                            <td>
                                {{ $withdrawal->user?->name ?? 'Deleted user' }}
                                <br>
                                <small>{{ $withdrawal->user?->email }}</small>
                            </td>
                            <td>₹{{ number_format((float) $withdrawal->amount, 2) }}</td>
                            <td>₹{{ number_format((float) $withdrawal->fee, 2) }}</td>
                            <td>₹{{ number_format((float) $withdrawal->net_amount, 2) }}</td>
                            <td>{{ $withdrawal->payment_method }}</td>
                            <td>
                                @php
                                    $badge = match($withdrawal->status) {
                                        'paid' => 'success',
                                        'rejected' => 'danger',
                                        'approved' => 'primary',
                                        default => 'warning',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.withdrawals.show', $withdrawal) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                No withdrawal requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $withdrawals->links() }}</div>
</div>
@endsection
