<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Member Portal')</title>
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}">
    <style>
        body{background:#f5f7fb}.member-nav{min-height:68px;background:#fff;border-bottom:1px solid #e8edf4;display:flex;align-items:center;justify-content:space-between;padding:12px clamp(16px,4vw,48px);gap:18px}.member-brand{color:#24366e;font-size:18px;font-weight:700;text-decoration:none}.member-links{display:flex;align-items:center;flex-wrap:wrap;gap:8px}.member-links a{padding:9px 11px;border-radius:9px;color:#536176;text-decoration:none;font-size:13px;font-weight:600}.member-links a:hover,.member-links a.active{background:#eef2ff;color:#3157cb}.member-content{max-width:1200px;margin:0 auto;padding:clamp(18px,4vw,40px)}.member-card{padding:24px;border:1px solid #e8edf4;border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(31,42,68,.04)}.member-alert{padding:14px 16px;border-radius:12px;margin-bottom:18px;background:#effcf5;color:#176b42}.member-error{background:#fff5f4;color:#b42318}.member-user{display:flex;align-items:center;gap:10px;color:#697586;font-size:13px}@media(max-width:760px){.member-nav{align-items:flex-start;flex-direction:column}.member-content{padding:18px 12px}.member-card{padding:16px}}
    </style>
</head>
<body>
    <header class="member-nav">
        <a class="member-brand" href="{{ route('plans.index') }}">Member Portal</a>
        <nav class="member-links" aria-label="Member navigation">
            <a class="{{ request()->routeIs('plans.*') ? 'active' : '' }}" href="{{ route('plans.index') }}">Plans</a>
            <a class="{{ request()->routeIs('subscriptions.mine') ? 'active' : '' }}" href="{{ route('subscriptions.mine') }}">My subscriptions</a>
            <a class="{{ request()->routeIs('profits.mine') ? 'active' : '' }}" href="{{ route('profits.mine') }}">Daily profits</a>
            <a class="{{ request()->routeIs('withdrawals.mine') ? 'active' : '' }}" href="{{ route('withdrawals.mine') }}">Withdrawals</a>
        </nav>
        <div class="member-user">
            <span>{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">Log out</button>
            </form>
        </div>
    </header>
    <main class="member-content">
        @if (session('success'))
            <div class="member-alert" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="member-alert member-error" role="alert">
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
