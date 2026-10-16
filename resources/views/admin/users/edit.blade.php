
@extends('admin.layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
    <h3>Edit User</h3>
    <p class="text-muted">User ID: {{ $user->id }}</p>
    </div>
    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">View details</a>
</div>

<div class="admin-card">
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input
                id="name"
                type="text"
                name="name"
                class="form-control"
                value="{{ old('name', $user->name) }}"
                required
            >
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                class="form-control"
                value="{{ old('email', $user->email) }}"
                required
            >
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Account Status</label>
            <select id="status" name="status" class="form-select" required>
                <option value="active" @selected(old('status', $user->status) === 'active')>
                    Active
                </option>
                <option value="suspended" @selected(old('status', $user->status) === 'suspended')>
                    Suspended
                </option>
            </select>
            @if (auth()->user()->is($user))
                <div class="form-text">You cannot suspend your own administrator account.</div>
            @endif
        </div>

        <button class="btn btn-primary">Save Changes</button>

        <a href="{{ route('admin.users.show', $user) }}"
           class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
