<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Console')</title>
    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}">
    <style>
        body { background: #f5f7fb; }
        .admin-topbar { min-height: 72px; display:flex; align-items:center; justify-content:space-between; gap:16px; padding:12px clamp(18px, 4vw, 48px); background:#fff; border-bottom:1px solid #e8edf4; }
        .admin-brand { display:flex; align-items:center; gap:12px; color:#1f2a44; font-size:17px; font-weight:700; text-decoration:none; }
        .admin-brand-mark { width:38px; height:38px; display:grid; place-items:center; border-radius:12px; color:#fff; background:#3157cb; }
        .admin-account { display:flex; align-items:center; gap:14px; }
        .admin-account-name { color:#667085; font-size:13px; }
        .admin-shell { min-height:calc(100vh - 72px); display:grid; grid-template-columns:240px minmax(0,1fr); }
        .admin-sidebar { padding:26px 16px; border-right:1px solid #e8edf4; background:#fff; }
        .admin-nav-label { margin:0 12px 12px; color:#98a2b3; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase; }
        .admin-nav-link { display:flex; align-items:center; gap:12px; padding:12px; border-radius:10px; color:#586579; text-decoration:none; font-size:14px; font-weight:600; }
        .admin-nav-link:hover, .admin-nav-link.active { color:#3157cb; background:#eef2ff; }
        .admin-content { min-width:0; padding:clamp(20px, 4vw, 40px); }
        .admin-content-inner { width:min(100%, 1260px); margin:0 auto; }
        .admin-card { padding:24px; border:1px solid #e8edf4; border-radius:16px; background:#fff; box-shadow:0 8px 24px rgba(31,42,68,.04); }
        .admin-alert { margin-bottom:20px; padding:14px 16px; border:1px solid #b7ebd0; border-radius:12px; background:#effcf5; color:#176b42; }
        .admin-alert-error { border-color:#ffd2d0; background:#fff5f4; color:#b42318; }
        @media (max-width: 760px) { .admin-shell { grid-template-columns:1fr; } .admin-sidebar { padding:10px 14px; border-right:0; border-bottom:1px solid #e8edf4; } .admin-nav-label { display:none; } .admin-nav-link { display:inline-flex; } .admin-content { padding:20px 14px; } .admin-card { padding:16px; } .admin-account-name { display:none; } }
    </style>
</head>
<body>
    <header class="admin-topbar">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}">
            <span class="admin-brand-mark">✦</span>
            <span>Admin Console</span>
        </a>
        <div class="admin-account">
            <span class="admin-account-name">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="btn btn-outline-secondary btn-sm" type="submit">Sign out</button>
            </form>
        </div>
    </header>

    <div class="admin-shell">
        <aside class="admin-sidebar">
            <p class="admin-nav-label">Management</p>
            
            <a class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="ti ti-crown"></i><span>Dashboard</span>
            </a>
            
            <a class="admin-nav-link {{ request()->routeIs('admin.subscription-plans.*') ? 'active' : '' }}" href="{{ route('admin.subscription-plans.index') }}">
                <i class="ti ti-crown"></i><span>Subscription plans</span>
            </a>
         
            <a class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <i class="ti ti-users"></i><span>Users</span>
            </a>
            <a class="admin-nav-link {{ request()->routeIs('admin.recharges.*') ? 'active' : '' }}" href="{{ route('admin.recharges.index') }}">
                <i class="ti ti-wallet"></i><span>Recharge requests</span>
            </a>
            <a class="admin-nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}" href="{{ route('admin.subscriptions.index') }}">
                <i class="ti ti-wallet"></i><span> Purchased Subscriptions</span>
            </a>
            <a class="admin-nav-link {{ request()->routeIs('admin.daily-profits.*') ? 'active' : '' }}" href="{{ route('admin.daily-profits.index') }}">
                <i class="ti ti-wallet"></i><span> Daily Profits</span>
            </a>
            <a class="admin-nav-link {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}" href="{{ route('admin.withdrawals.index') }}">
                <i class="ti ti-wallet"></i><span> Withdrawals</span>
            </a>
            <a class="admin-nav-link {{ request()->routeIs('admin.wallet-transactions.*') ? 'active' : '' }}" href="{{ route('admin.wallet-transactions.index') }}">
                <i class="ti ti-wallet"></i><span> Wallet Transactions</span>
            </a>
            
        </aside>

        <main class="admin-content">
            <div class="admin-content-inner">
                @if (session('success'))
                    <div class="admin-alert" role="status">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="admin-alert admin-alert-error" role="alert">
                        <strong>Please review the form:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>