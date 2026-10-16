
@extends('admin.layouts.app')

@section('title', 'Edit Subscription Plan')

@section('content')

<div class="mb-4">
    <h3>Edit Subscription Plan</h3>

    <p class="text-muted mb-0">
        Update: {{ $plan->name }}
    </p>
</div>

<div class="admin-card">
    <form
        method="POST"
        action="{{ route('admin.subscription-plans.update', $plan) }}"
    >
        @csrf
        @method('PUT')

        @include('admin.subscription-plans._form', ['plan' => $plan])
    </form>
</div>

@endsection
