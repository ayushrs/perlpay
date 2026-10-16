
@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Withdrawal #{{ $withdrawal->id }}</h2>
        <a href="{{ route('admin.withdrawals.index') }}"
           class="btn btn-outline-secondary">Back</a>
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

    <div class="card mb-4">
        <div class="card-header">Request Details</div>
        <div class="card-body">
            <p><strong>User:</strong> {{ $withdrawal->user?->name ?? 'Deleted user' }}</p>
            <p><strong>Email:</strong> {{ $withdrawal->user?->email ?? '—' }}</p>
            <p><strong>Requested amount:</strong> ₹{{ number_format((float) $withdrawal->amount, 2) }}</p>
            <p><strong>Fee:</strong> ₹{{ number_format((float) $withdrawal->fee, 2) }}</p>
            <p><strong>Net payable:</strong> ₹{{ number_format((float) $withdrawal->net_amount, 2) }}</p>
            <p><strong>Payment method:</strong> {{ $withdrawal->payment_method }}</p>

            <p><strong>Payment details:</strong></p>
            <pre class="bg-light p-3 rounded">{{ json_encode($withdrawal->payment_details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

            <p><strong>Status:</strong> {{ ucfirst($withdrawal->status) }}</p>
            <p><strong>Reviewed by:</strong> {{ $withdrawal->reviewer?->name ?? '—' }}</p>
            <p><strong>Reviewed at:</strong> {{ $withdrawal->reviewed_at?->format('d M Y H:i') ?? '—' }}</p>
            <p><strong>Payment reference:</strong> {{ $withdrawal->payment_reference ?? '—' }}</p>
            <p><strong>Paid at:</strong> {{ $withdrawal->paid_at?->format('d M Y H:i') ?? '—' }}</p>
            <p><strong>Rejection reason:</strong> {{ $withdrawal->rejection_reason ?? '—' }}</p>
            <p class="mb-0"><strong>Created:</strong> {{ $withdrawal->created_at?->format('d M Y H:i') }}</p>
        </div>
    </div>

    @if($withdrawal->status === 'pending')
        <div class="card">
            <div class="card-header">Review Request</div>
            <div class="card-body">
                <form method="POST"
                      action="{{ route('admin.withdrawals.approve', $withdrawal) }}"
                      class="mb-3"
                      onsubmit="return confirm('Approve this request and reserve its full amount from the wallet?');">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        Approve & Reserve Amount
                    </button>
                </form>

                <form method="POST"
                      action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
                    @csrf
                    <label class="form-label" for="rejection_reason">
                        Rejection reason
                    </label>
                    <textarea id="rejection_reason" name="rejection_reason"
                              class="form-control mb-2" rows="3"
                              maxlength="1000" required>{{ old('rejection_reason') }}</textarea>
                    <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Reject this pending request?');">
                        Reject Request
                    </button>
                </form>
            </div>
        </div>
    @elseif($withdrawal->status === 'approved')
        <div class="card">
            <div class="card-header">Confirm Payment</div>
            <div class="card-body">
                <p>
                    Payment details verify karo aur external payment actually complete
                    hone ke baad hi is request ko paid mark karo.
                </p>

                <form method="POST"
                      action="{{ route('admin.withdrawals.mark-paid', $withdrawal) }}">
                    @csrf
                    <label for="payment_reference" class="form-label">
                        Bank / UPI / payment reference
                    </label>
                    <input id="payment_reference" name="payment_reference"
                           class="form-control mb-3" maxlength="255"
                           value="{{ old('payment_reference') }}" required>

                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Confirm that the payment has actually been sent?');">
                        Mark as Paid
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
