<?php

namespace App\Console\Commands;

use App\Models\DailyProfit;
use App\Models\UserSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateDailyProfits extends Command
{
    protected $signature = 'profits:generate {--date= : Generate entries through this date (YYYY-MM-DD)}';

    protected $description = 'Create one pending daily profit entry per active subscription and eligible day';

    public function handle(): int
    {
        try {
            $throughDate = $this->option('date')
                ? Carbon::parse($this->option('date'))->startOfDay()
                : now()->subDay()->startOfDay();
        } catch (\Throwable) {
            $this->error('Invalid date. Use YYYY-MM-DD.');

            return self::FAILURE;
        }

        $created = 0;

        UserSubscription::query()
            ->where('status', 'active')
            ->whereNotNull('starts_at')
            ->whereNotNull('ends_at')
            ->whereDate('starts_at', '<=', $throughDate->toDateString())
            ->chunkById(200, function ($subscriptions) use ($throughDate, &$created): void {
                foreach ($subscriptions as $subscription) {
                    $startDate = $subscription->starts_at->copy()->startOfDay();
                    $lastEligibleDate = $subscription->ends_at->copy()
                        ->subDay()
                        ->startOfDay();
                    $lastDate = $lastEligibleDate->lessThan($throughDate)
                        ? $lastEligibleDate
                        : $throughDate;

                    if ($lastDate->lessThan($startDate)) {
                        continue;
                    }

                    DB::transaction(function () use ($subscription, $startDate, $lastDate, &$created): void {
                        $date = $startDate->copy();

                        while ($date->lessThanOrEqualTo($lastDate)) {
                            $profit = DailyProfit::firstOrCreate(
                                [
                                    'user_subscription_id' => $subscription->id,
                                    'profit_date' => $date->toDateString(),
                                ],
                                [
                                    'user_id' => $subscription->user_id,
                                    'amount' => $subscription->daily_income,
                                    'status' => 'pending',
                                ]
                            );

                            if ($profit->wasRecentlyCreated) {
                                $created++;
                            }

                            $date->addDay();
                        }

                        if ($subscription->ends_at->lessThanOrEqualTo(now())) {
                            UserSubscription::whereKey($subscription->id)
                                ->where('status', 'active')
                                ->update(['status' => 'completed']);
                        }
                    }, 3);
                }
            });

        $this->info("Created {$created} daily profit entr".($created === 1 ? 'y' : 'ies').'.');

        return self::SUCCESS;
    }
}
