<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Admin Login</title>
    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { font-family: 'DM Sans', sans-serif; color: #182230; background: #f4f7fc; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; padding: 28px; background: radial-gradient(circle at 12% 12%, #e5edff 0, transparent 32%), radial-gradient(circle at 90% 90%, #dff7f1 0, transparent 30%), #f7f9fc; }
        .login-shell { width: min(100%, 990px); min-height: 590px; display: grid; grid-template-columns: 1fr 1fr; overflow: hidden; border: 1px solid #e8edf5; border-radius: 28px; background: #fff; box-shadow: 0 28px 80px rgba(34, 54, 89, .12); }
        .brand-panel { position: relative; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; padding: 48px; color: #fff; background: linear-gradient(145deg, #172554, #273f8f 58%, #2c65a2); }
        .brand-panel::before, .brand-panel::after { position: absolute; content: ''; border: 1px solid rgba(255,255,255,.12); border-radius: 50%; }
        .brand-panel::before { width: 390px; height: 390px; right: -170px; top: 120px; }
        .brand-panel::after { width: 270px; height: 270px; right: -110px; top: 180px; }
        .brand-mark, .brand-copy, .brand-foot { position: relative; z-index: 1; }
        .brand-mark { display: flex; align-items: center; gap: 12px; font: 800 18px 'Manrope', sans-serif; letter-spacing: -.4px; }
        .brand-mark span { display: grid; width: 42px; height: 42px; place-items: center; border: 1px solid rgba(255,255,255,.32); border-radius: 13px; background: rgba(255,255,255,.12); }
        .brand-copy { max-width: 380px; margin-top: 70px; }
        .brand-copy .eyebrow { color: #b9d4ff; font-size: 12px; font-weight: 700; letter-spacing: 1.8px; text-transform: uppercase; }
        .brand-copy h1 { margin: 14px 0; font: 800 clamp(32px, 4vw, 46px)/1.12 'Manrope', sans-serif; letter-spacing: -1.8px; }
        .brand-copy p, .brand-foot { color: rgba(255,255,255,.72); line-height: 1.7; }
        .brand-foot { font-size: 12px; }
        .form-panel { display: flex; flex-direction: column; justify-content: center; padding: 56px clamp(28px, 6vw, 68px); }
        .form-panel h2 { margin: 0 0 8px; font: 800 30px 'Manrope', sans-serif; letter-spacing: -.8px; }
        .intro { margin: 0 0 34px; color: #778399; }
        .field { margin-bottom: 20px; }
        .field label { display: block; margin-bottom: 8px; color: #344054; font-size: 13px; font-weight: 700; }
        .field input { width: 100%; height: 50px; padding: 0 14px; border: 1px solid #dce3ee; border-radius: 12px; outline: none; background: #fff; color: #182230; font: inherit; transition: border-color .2s, box-shadow .2s; }
        .field input:focus { border-color: #4169d8; box-shadow: 0 0 0 4px rgba(65,105,216,.12); }
        .remember { display: flex; align-items: center; gap: 9px; margin: 2px 0 24px; color: #596579; font-size: 13px; }
        .remember input { width: 16px; height: 16px; accent-color: #365bd1; }
        .submit { width: 100%; min-height: 50px; border: 0; border-radius: 12px; background: #3157cb; color: #fff; cursor: pointer; font: 700 14px 'DM Sans', sans-serif; box-shadow: 0 10px 22px rgba(49,87,203,.2); transition: transform .2s, background .2s; }
        .submit:hover { transform: translateY(-1px); background: #2749b2; }
        .error { margin: 0 0 18px; padding: 12px 14px; border: 1px solid #ffd2d0; border-radius: 10px; background: #fff5f4; color: #b42318; font-size: 13px; }
        .hint { margin: 22px 0 0; color: #8a94a6; text-align: center; font-size: 12px; line-height: 1.6; }
        @media (max-width: 720px) { body { padding: 16px; } .login-shell { max-width: 480px; min-height: 0; grid-template-columns: 1fr; border-radius: 22px; } .brand-panel { min-height: 190px; padding: 26px; } .brand-copy { margin-top: 30px; } .brand-copy h1 { max-width: 340px; font-size: 30px; } .brand-copy p, .brand-foot { display: none; } .form-panel { padding: 34px 26px; } }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="brand-panel" aria-label="Admin portal">
            <div class="brand-mark"><span>✦</span> Admin Console</div>
            <div class="brand-copy">
                <div class="eyebrow">Secure workspace</div>
                <h1>Welcome back to your control center.</h1>
                <p>Sign in to manage subscription plans and keep your platform running smoothly.</p>
            </div>
            <div class="brand-foot">Authorized administrators only</div>
        </section>

        <section class="form-panel">
            <h2>Admin sign in</h2>
            <p class="intro">Enter your administrator credentials to continue.</p>

            @if ($errors->any())
                <div class="error" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf
                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="admin@example.com" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" placeholder="Enter your password" required>
                </div>
                <label class="remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                <button class="submit" type="submit">Sign in to admin</button>
            </form>
            <p class="hint">Access is limited to active administrator accounts.</p>
        </section>
    </main>
</body>
</html>