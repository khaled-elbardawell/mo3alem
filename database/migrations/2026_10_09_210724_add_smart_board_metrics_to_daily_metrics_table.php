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
            $table->unsignedBigInteger('smart_boards_created')->default(0)->after('certificate_saved');
            $table->unsignedBigInteger('smart_board_sessions')->default(0)->after('smart_boards_created');
            $table->unsignedBigInteger('smart_board_responses')->default(0)->after('smart_board_sessions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_metrics', function (Blueprint $table) {
            $table->dropColumn([
                'smart_boards_created',
                'smart_board_sessions',
                'smart_board_responses',
            ]);
        });
    }
};
