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
        Schema::create('short_link_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_link_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedBigInteger('visits')->default(0);
            $table->unsignedBigInteger('unique_visits')->default(0);
            $table->timestamps();

            $table->unique(['short_link_id', 'date']);
            $table->index(['date', 'visits']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_link_daily_stats');
    }
};
