@extends('layouts.user')

@section('title', 'My Daily Profits')

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Daily profits</h2>
    <p class="text-muted mb-0">Entries stay pending until reviewed and credited by an administrator.</p>
</div>
<div class="member-card">
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>Date</th><th>Plan</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($profits as $profit)
            <tr>
                <td>{{ $profit->profit_date?->format('d M Y') ?? '—' }}</td>
                <td>{{ $profit->subscription?->plan?->name ?? 'Plan unavailable' }}</td>
                <td>₹{{ number_format((float) $profit->amount, 2) }}</td>
                <td>{{ ucfirst($profit->status) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center py-4">No daily profit entries recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $profits->links() }}
</div>
@endsection
