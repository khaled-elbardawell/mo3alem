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
            $table->unsignedBigInteger('certificate_generated')->default(0)->after('qr_saved');
            $table->unsignedBigInteger('certificate_saved')->default(0)->after('certificate_generated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_metrics', function (Blueprint $table) {
            $table->dropColumn(['certificate_generated', 'certificate_saved']);
        });
    }
};
