
@extends('admin.layouts.app')

@section('title', 'Manage Users')

@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="mb-1">Users Management</h3>
        <p class="text-muted mb-0">Manage registered users and their accounts.</p>
    </div>
</div>

<div class="admin-card">
    <form method="GET" action="{{ route('admin.users.index') }}"
          class="row g-2 mb-4">

        <div class="col-md-6">
            <input
                type="text"
                name="search"
                class="form-control"
                aria-label="Search users by name or email"
                placeholder="Search name or email..."
                value="{{ request('search') }}"
            >
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select" aria-label="Filter users by status">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>
                    Active
                </option>
                <option value="suspended" @selected(request('status') === 'suspended')>
                    Suspended
                </option>
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary">Search</button>
            <a href="{{ route('admin.users.index') }}"
               class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Wallet Balance</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            {{ $user->name }}
                            @if($user->is_admin)
                                <span class="badge text-bg-primary">Admin</span>
                            @endif
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            ₹{{ number_format((float) ($user->wallet?->balance ?? 0), 2) }}
                        </td>
                        <td>
                            <span class="badge {{ $user->status === 'active' ? 'text-bg-success' : 'text-bg-danger' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td>{{ $user->created_at?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.users.show', $user) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    View
                                </a>

                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            No users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>
@endsection
