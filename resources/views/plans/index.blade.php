```blade
@extends('layouts.user')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Available Plans</h2>
        <a href="{{ route('subscriptions.mine') }}" class="btn btn-outline-primary">
            My Subscriptions
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="alert alert-info">
        Wallet Balance:
        <strong>₹{{ number_format((float) ($wallet?->balance ?? 0), 2) }}</strong>
    </div>

    <div class="row g-4">
        @forelse($plans as $plan)
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5>{{ $plan->name }}</h5>

                        @if($plan->is_vip)
                            <span class="badge bg-warning text-dark">VIP</span>
                        @endif

                        <hr>

                        <p>Price:
                            <strong>₹{{ number_format((float) $plan->price, 2) }}</strong>
                        </p>

                        <p>Duration: {{ $plan->duration_days }} days</p>

                        <p>Listed daily amount:
                            ₹{{ number_format((float) $plan->daily_income, 2) }}
                        </p>

                        <p>Listed total amount:
                            ₹{{ number_format((float) $plan->total_income, 2) }}
                        </p>

                        <form method="POST"
                              action="{{ route('plans.purchase', $plan) }}"
                              onsubmit="return confirm('Purchase this plan using your wallet balance?');">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                Purchase Plan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-secondary">No plans are available.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
```