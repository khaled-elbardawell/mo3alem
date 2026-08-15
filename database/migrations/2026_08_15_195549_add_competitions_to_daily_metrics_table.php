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
        Schema::table('daily_metrics', function (Blueprint $table) {
            $table->unsignedBigInteger('competitions')->default(0)->after('active_users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_metrics', function (Blueprint $table) {
            $table->dropColumn('competitions');
        });
    }
};
