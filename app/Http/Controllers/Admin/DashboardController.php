<?php

namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;
use App\Models\DailyProfit;
use App\Models\RechargeRequest;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // User statistics
        $totalUsers = User::where('is_admin', false)->count();

        $activeUsers = User::where('is_admin', false)
            ->where('status', 'active')
            ->count();

        // Subscription statistics
        $activeSubscriptions = UserSubscription::where('status', 'active')
            ->count();

        $totalPlans = SubscriptionPlan::where('is_active', true)->count();

        // Pending requests
        $pendingRecharges = RechargeRequest::where('status', 'pending')
            ->count();

        $pendingWithdrawals = Withdrawal::where('status', 'pending')
            ->count();

        $pendingProfits = DailyProfit::where('status', 'pending')
            ->count();

        // Financial totals: completed ledger entries only
        $completedCredits = WalletTransaction::where('status', 'completed')
            ->where('direction', 'credit')
            ->sum('amount');

        $completedDebits = WalletTransaction::where('status', 'completed')
            ->where('direction', 'debit')
            ->sum('amount');

        $todayCredits = WalletTransaction::where('status', 'completed')
            ->where('direction', 'credit')
            ->whereDate('created_at', today())
            ->sum('amount');

        $todayDebits = WalletTransaction::where('status', 'completed')
            ->where('direction', 'debit')
            ->whereDate('created_at', today())
            ->sum('amount');

        $todayTransactions = WalletTransaction::where('status', 'completed')
            ->whereDate('created_at', today())
            ->count();

        // Recent transaction activity
        $recentTransactions = WalletTransaction::with('wallet.user')
            ->latest('id')
            ->limit(10)
            ->get();

        // Latest users
        $recentUsers = User::where('is_admin', false)
            ->latest('id')
            ->limit(5)
            ->get();

        // Transaction type summary for completed entries
        $transactionTypes = WalletTransaction::query()
            ->select('type', DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(amount) as total_amount'))
            ->where('status', 'completed')
            ->groupBy('type')
            ->orderByDesc('total_amount')
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'activeSubscriptions',
            'totalPlans',
            'pendingRecharges',
            'pendingWithdrawals',
            'pendingProfits',
            'completedCredits',
            'completedDebits',
            'todayCredits',
            'todayDebits',
            'todayTransactions',
            'recentTransactions',
            'recentUsers',
            'transactionTypes'
        ));
    }
}