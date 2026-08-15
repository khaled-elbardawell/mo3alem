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
        Schema::create('unique_metric_events', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->char('event_hash', 64);
            $table->timestamps();

            $table->unique(['date', 'event_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unique_metric_events');
    }
};
