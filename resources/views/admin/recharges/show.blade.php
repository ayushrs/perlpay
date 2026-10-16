
@extends('admin.layouts.app')

@section('title', 'Recharge Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3>Recharge Request #{{ $recharge->id }}</h3>
        <p class="text-muted mb-0">Review payment details before taking action.</p>
    </div>

    <a href="{{ route('admin.recharges.index') }}"
       class="btn btn-outline-secondary">Back</a>
</div>

<div class="admin-card mb-4">
    <h5>Request Details</h5>
    <hr>

    <p><strong>User:</strong> {{ $recharge->user?->name ?? 'Unknown' }}</p>
    <p><strong>Email:</strong> {{ $recharge->user?->email ?? '-' }}</p>
    <p><strong>Amount:</strong> ₹{{ number_format((float) $recharge->amount, 2) }}</p>
    <p><strong>Payment Method:</strong> {{ $recharge->payment_method ?: '-' }}</p>
    <p><strong>Payment Reference:</strong> {{ $recharge->payment_reference ?: '-' }}</p>
    <p><strong>Submitted:</strong> {{ $recharge->created_at?->format('d M Y, h:i A') ?? '—' }}</p>
    <p><strong>Status:</strong> {{ ucfirst($recharge->status) }}</p>

    @if($recharge->reviewer)
        <p><strong>Reviewed By:</strong> {{ $recharge->reviewer->name }}</p>
    @endif

    @if($recharge->admin_note)
        <p><strong>Admin Note:</strong> {{ $recharge->admin_note }}</p>
    @endif

    @if($recharge->proof_path)
        <p>
            <strong>Payment Proof:</strong>
            <a href="{{ route('admin.recharges.proof', $recharge) }}">Download proof securely</a>
        </p>
    @endif

    @if($recharge->walletTransaction)
        <hr>
        <h6>Wallet Transaction</h6>
        <p><strong>Transaction ID:</strong> {{ $recharge->walletTransaction->id }}</p>
        <p><strong>Balance Before:</strong>
            ₹{{ number_format((float) $recharge->walletTransaction->balance_before, 2) }}
        </p>
        <p><strong>Balance After:</strong>
            ₹{{ number_format((float) $recharge->walletTransaction->balance_after, 2) }}
        </p>
    @endif
</div>

@if($recharge->status === 'pending')
    <div class="row g-4">
        <div class="col-md-6">
            <div class="admin-card h-100">
                <h5 class="text-success">Approve Recharge</h5>
                <p class="text-muted">
                    Verify that the payment has actually been received
                    before crediting the user's wallet.
                </p>

                <form
                    method="POST"
                    action="{{ route('admin.recharges.approve', $recharge) }}"
                    onsubmit="return confirm('Have you verified this payment? Approve and credit the wallet?')"
                >
                    @csrf

                    <button class="btn btn-success">
                        Approve & Credit Wallet
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="admin-card h-100">
                <h5 class="text-danger">Reject Recharge</h5>

                <form
                    method="POST"
                    action="{{ route('admin.recharges.reject', $recharge) }}"
                    class="mt-3"
                >
                    @csrf

                    <label class="form-label">Reason for rejection</label>

                    <textarea
                        name="admin_note"
                        class="form-control mb-3"
                        rows="3"
                        maxlength="1000"
                        required
                    >{{ old('admin_note') }}</textarea>

                    <button class="btn btn-danger">
                        Reject Request
                    </button>
                </form>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-info">
        This recharge has already been processed. No further action is available.
    </div>
@endif
@endsection
