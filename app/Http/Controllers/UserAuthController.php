<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserAuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($credentials['remember'] ?? false);
        unset($credentials['remember']);

        if (! Auth::attempt([
            ...$credentials,
            'is_admin' => false,
            'status' => 'active',
        ], $remember)) {
            return back()
                ->withErrors(['email' => 'The credentials are invalid or this account is inactive.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('plans.index'));
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function createAccount(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($data): User {
            $user = User::create($data);
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'lifetime_credited' => 0,
                'lifetime_debited' => 0,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('plans.index')
            ->with('success', 'Account created. Add funds to your wallet to purchase a plan.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
