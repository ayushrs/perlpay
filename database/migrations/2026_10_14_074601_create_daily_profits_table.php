<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('daily_profits')) {
            $indexes = collect(Schema::getIndexes('daily_profits'))
                ->pluck('name')
                ->all();
            $foreignKeys = collect(Schema::getForeignKeys('daily_profits'))
                ->pluck('name')
                ->all();

            Schema::table('daily_profits', function (Blueprint $table) use ($indexes, $foreignKeys): void {
                if (! in_array('daily_profits_user_subscription_id_profit_date_unique', $indexes, true)) {
                    $table->unique(
                        ['user_subscription_id', 'profit_date'],
                        'daily_profits_user_subscription_id_profit_date_unique'
                    );
                }

                if (! in_array('daily_profits_wallet_transaction_id_foreign', $foreignKeys, true)) {
                    $table->foreign('wallet_transaction_id', 'daily_profits_wallet_transaction_id_foreign')
                        ->references('id')
                        ->on('wallet_transactions')
                        ->restrictOnDelete();
                }
            });

            return;
        }

        Schema::create('daily_profits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_subscription_id')
                ->constrained('user_subscriptions')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            $table->date('profit_date');
            $table->decimal('amount', 14, 2);

            $table->string('status')->default('pending');
            // pending, approved, rejected

            $table->foreignId('wallet_transaction_id')
                ->nullable()
                ->constrained('wallet_transactions')
                ->restrictOnDelete();

            $table->timestamps();

            $table->unique(
                ['user_subscription_id', 'profit_date'],
                'daily_profits_user_subscription_id_profit_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_profits');
    }
};