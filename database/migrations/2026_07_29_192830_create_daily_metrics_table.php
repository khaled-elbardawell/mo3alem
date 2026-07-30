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
        Schema::create('daily_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedBigInteger('registrations')->default(0);
            $table->unsignedBigInteger('active_users')->default(0);
            $table->unsignedBigInteger('saved_wheels')->default(0);
            $table->unsignedBigInteger('names_saved')->default(0);
            $table->unsignedBigInteger('spins')->default(0);
            $table->unsignedBigInteger('imports')->default(0);
            $table->unsignedBigInteger('ad_impressions')->default(0);
            $table->unsignedBigInteger('ad_clicks')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_metrics');
    }
};
