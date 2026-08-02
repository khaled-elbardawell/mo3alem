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
            $table->unsignedBigInteger('qr_generated')->default(0)->after('imports');
            $table->unsignedBigInteger('qr_saved')->default(0)->after('qr_generated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_metrics', function (Blueprint $table) {
            $table->dropColumn(['qr_generated', 'qr_saved']);
        });
    }
};
