<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(10);

        return view(
            'admin.subscription-plans.index',
            compact('plans')
        );
    }

    public function create()
    {
        return view('admin.subscription-plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required', 'string', 'max:120', 'alpha_dash',
                'unique:subscription_plans,slug',
            ],
            'price' => ['required', 'numeric', 'min:0.01'],
            'daily_income' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'is_vip' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $data['total_income'] =
            round(
                (float) $data['daily_income']
                * (int) $data['duration_days'],
                2
            );

        SubscriptionPlan::create($data);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan created successfully.');
    }

    public function edit(SubscriptionPlan $subscription_plan)
    {
        return view(
            'admin.subscription-plans.edit',
            ['plan' => $subscription_plan]
        );
    }

    public function update(
        Request $request,
        SubscriptionPlan $subscription_plan
    ) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required', 'string', 'max:120', 'alpha_dash',
                Rule::unique('subscription_plans', 'slug')
                    ->ignore($subscription_plan->id),
            ],
            'price' => ['required', 'numeric', 'min:0.01'],
            'daily_income' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'is_vip' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $data['total_income'] =
            round(
                (float) $data['daily_income']
                * (int) $data['duration_days'],
                2
            );

        $subscription_plan->update($data);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan updated successfully.');
    }

    public function destroy(SubscriptionPlan $subscription_plan)
    {
        // Historical subscription records safe rakhne ke liye
        // plan ko delete nahi, deactivate karte hain.
        $subscription_plan->update(['is_active' => false]);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan deactivated successfully.');
    }
}