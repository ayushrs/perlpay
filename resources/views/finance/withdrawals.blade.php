@extends('layouts.user')

@section('title', 'My Withdrawals')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Withdrawals</h2>
        <p class="text-muted mb-0">Request a payout and follow its review status.</p>
    </div>
    <div class="member-card py-2 px-3">Available balance: <strong>₹{{ number_format((float) ($wallet?->balance ?? 0), 2) }}</strong></div>
</div>

<div class="member-card mb-4">
    <h5 class="mb-3">New withdrawal request</h5>
    <p class="small text-muted">No fee is currently configured. Funds are deducted only after admin approval; pending requests reduce the amount you can request again.</p>
    <form method="POST" action="{{ route('withdrawals.store') }}" class="row g-3">
        @csrf
        <div class="col-md-4">
            <label for="amount" class="form-label">Amount (₹)</label>
            <input id="amount" name="amount" type="number" class="form-control" min="1" step="0.01" max="{{ number_format((float) ($wallet?->balance ?? 0), 2, '.', '') }}" value="{{ old('amount') }}" required>
        </div>
        <div class="col-md-4">
            <label for="payment_method" class="form-label">Payment method</label>
            <select id="payment_method" name="payment_method" class="form-select" required>
                <option value="">Choose method</option>
                <option value="upi" @selected(old('payment_method') === 'upi')>UPI</option>
                <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Bank transfer</option>
            </select>
        </div>
        <div class="col-md-4" id="upi-fields">
            <label for="upi_id" class="form-label">UPI ID</label>
            <input id="upi_id" name="upi_id" class="form-control" maxlength="255" value="{{ old('upi_id') }}" placeholder="name@bank">
        </div>
        <div class="col-md-4 bank-fields">
            <label for="account_name" class="form-label">Account holder name</label>
            <input id="account_name" name="account_name" class="form-control" maxlength="255" value="{{ old('account_name') }}">
        </div>
        <div class="col-md-4 bank-fields">
            <label for="account_number" class="form-label">Account number</label>
            <input id="account_number" name="account_number" class="form-control" maxlength="34" value="{{ old('account_number') }}">
        </div>
        <div class="col-md-4 bank-fields">
            <label for="ifsc" class="form-label">IFSC code</label>
            <input id="ifsc" name="ifsc" class="form-control" maxlength="20" value="{{ old('ifsc') }}">
        </div>
        <div class="col-12"><button class="btn btn-primary" type="submit">Submit withdrawal request</button></div>
    </form>
</div>

<div class="member-card">
    <h5 class="mb-3">Request history</h5>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>Date</th><th>Amount</th><th>Net payable</th><th>Method</th><th>Status</th><th>Payment reference</th><th>Note</th></tr></thead>
        <tbody>
        @forelse($withdrawals as $withdrawal)
            <tr>
                <td>{{ $withdrawal->created_at?->format('d M Y') ?? '—' }}</td>
                <td>₹{{ number_format((float) $withdrawal->amount, 2) }}</td>
                <td>₹{{ number_format((float) $withdrawal->net_amount, 2) }}</td>
                <td>{{ str_replace('_', ' ', ucfirst($withdrawal->payment_method ?? '—')) }}</td>
                <td>{{ ucfirst($withdrawal->status) }}</td>
                <td>{{ $withdrawal->payment_reference ?? '—' }}</td>
                <td>{{ $withdrawal->rejection_reason ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center py-4">No withdrawal requests yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $withdrawals->links() }}
</div>
<script>
    (() => {
        const method = document.getElementById('payment_method');
        const upi = document.getElementById('upi-fields');
        const bankFields = document.querySelectorAll('.bank-fields');
        const update = () => {
            const isUpi = method.value === 'upi';
            upi.hidden = !isUpi;
            bankFields.forEach((field) => { field.hidden = method.value !== 'bank_transfer'; });
        };
        method.addEventListener('change', update);
        update();
    })();
</script>
@endsection
