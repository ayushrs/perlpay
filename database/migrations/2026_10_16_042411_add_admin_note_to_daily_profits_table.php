<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('daily_profits', function (Blueprint $table) {
        $table->text('admin_note')->nullable();
    });
}

public function down(): void
{
    Schema::table('daily_profits', function (Blueprint $table) {
        $table->dropColumn('admin_note');
    });
}
};
