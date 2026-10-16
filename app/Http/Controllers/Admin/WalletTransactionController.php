<?php

namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WalletTransactionController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => [
                'nullable',
                Rule::in([
                    'recharge',
                    'subscription_purchase',
                    'daily_profit',
                    'withdrawal',
                ]),
            ],
            'direction' => ['nullable', Rule::in(['credit', 'debit'])],
            'status' => [
                'nullable',
                Rule::in(['pending', 'completed', 'failed', 'rejected']),
            ],
            'from' => ['nullable', 'date', 'required_with:to', 'before_or_equal:to'],
            'to' => ['nullable', 'date', 'required_with:from', 'after_or_equal:from'],
        ]);

        $query = WalletTransaction::query()
            ->with(['wallet.user']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('wallet.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['direction'])) {
            $query->where('direction', $filters['direction']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        // Summary current filters ke mutabik hai.
        // Sirf completed transactions financial totals mein count hongi.
        $totalCredit = (clone $query)
            ->where('direction', 'credit')
            ->where('status', 'completed')
            ->sum('amount');

        $totalDebit = (clone $query)
            ->where('direction', 'debit')
            ->where('status', 'completed')
            ->sum('amount');

        $completedCount = (clone $query)
            ->where('status', 'completed')
            ->count();

        $transactions = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.wallet-transactions.index', compact(
            'transactions',
            'filters',
            'totalCredit',
            'totalDebit',
            'completedCount'
        ));
    }

    public function show(WalletTransaction $walletTransaction)
    {
        $walletTransaction->load(['wallet.user']);

        return view('admin.wallet-transactions.show', compact(
            'walletTransaction'
        ));
    }
}