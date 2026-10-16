
@extends('admin.layouts.app')

@section('title', 'Create Subscription Plan')

@section('content')

<div class="mb-4">
    <h3>Create Subscription Plan</h3>
    <p class="text-muted mb-0">Add a new normal or VIP plan.</p>
</div>

<div class="admin-card">
    <form
        method="POST"
        action="{{ route('admin.subscription-plans.store') }}"
    >
        @csrf

        @include('admin.subscription-plans._form')
    </form>
</div>

@endsection
