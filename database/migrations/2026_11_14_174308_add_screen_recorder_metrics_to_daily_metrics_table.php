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
            $table->unsignedBigInteger('screen_recordings_started')->default(0)->after('smart_board_responses');
            $table->unsignedBigInteger('screen_recordings_exported')->default(0)->after('screen_recordings_started');
            $table->unsignedBigInteger('screen_recordings_failed')->default(0)->after('screen_recordings_exported');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_metrics', function (Blueprint $table) {
            $table->dropColumn([
                'screen_recordings_started',
                'screen_recordings_exported',
                'screen_recordings_failed',
            ]);
        });
    }
};
