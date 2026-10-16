<?php

namespace App\Http\Controllers;

use App\Models\DailyProfit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserDailyProfitController extends Controller
{
    public function index(Request $request): View
    {
        $profits = DailyProfit::with('subscription.plan')
            ->where('user_id', $request->user()->id)
            ->latest('profit_date')
            ->latest('id')
            ->paginate(15);

        return view('finance.profits', compact('profits'));
    }
}
