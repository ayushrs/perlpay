<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with('wallet')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if (in_array($request->status, ['active', 'suspended'], true)) {
                    $query->where('status', $request->status);
                }
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load([
            'wallet.transactions' => fn ($query) => $query
                ->latest()
                ->limit(10),
            'subscriptions.plan',
        ]);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'status' => ['required', Rule::in(['active', 'suspended'])],
        ]);

        // Admin apni profile details update kar sakta hai, par khud ko suspend nahi.
        if ($request->user()->is($user) && $data['status'] !== $user->status) {
            return back()
                ->withErrors(['status' => 'You cannot change your own account status.'])
                ->withInput();
        }

        // Admin role ko is form se change nahi karna.
        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }
}