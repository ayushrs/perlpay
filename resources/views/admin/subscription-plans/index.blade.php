
@extends('admin.layouts.app')

@section('title', 'Manage Subscription Plans')

@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="mb-1">Subscription Plans</h3>
        <p class="text-muted mb-0">Manage normal and VIP plans</p>
    </div>

    <a
        href="{{ route('admin.subscription-plans.create') }}"
        class="btn btn-primary"
    >
        + Add New Plan
    </a>
</div>

<div class="admin-card">

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Plan</th>
                    <th>Price</th>
                    <th>Daily Income</th>
                    <th>Days</th>
                    <th>Total Income</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($plans as $plan)
                    <tr>
                        <td>{{ $plans->firstItem() + $loop->index }}</td>

                        <td>
                            <strong>{{ $plan->name }}</strong>
                            <div class="small text-muted">
                                {{ $plan->slug }}
                            </div>
                        </td>

                        <td>₹{{ number_format((float) $plan->price, 2) }}</td>

                        <td>₹{{ number_format((float) $plan->daily_income, 2) }}</td>

                        <td>{{ $plan->duration_days }}</td>

                        <td>₹{{ number_format((float) $plan->total_income, 2) }}</td>

                        <td>
                            @if ($plan->is_vip)
                                <span class="badge text-bg-warning">VIP</span>
                            @else
                                <span class="badge text-bg-secondary">Normal</span>
                            @endif
                        </td>

                        <td>
                            @if ($plan->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-danger">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex gap-2">
                                <a
                                    href="{{ route('admin.subscription-plans.edit', $plan) }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Edit
                                </a>

                                @if ($plan->is_active)
                                    <form
                                        method="POST"
                                        action="{{ route('admin.subscription-plans.destroy', $plan) }}"
                                        onsubmit="return confirm('Deactivate this plan?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                        >
                                            Deactivate
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            No subscription plans found.
                            <a href="{{ route('admin.subscription-plans.create') }}">
                                Create your first plan
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $plans->links() }}
    </div>

</div>

@endsection
