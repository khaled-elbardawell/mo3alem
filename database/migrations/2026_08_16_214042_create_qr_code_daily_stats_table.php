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
        Schema::create('qr_code_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_code_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedBigInteger('scans')->default(0);
            $table->unsignedBigInteger('unique_scans')->default(0);
            $table->timestamps();

            $table->unique(['qr_code_id', 'date']);
            $table->index(['date', 'scans']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_code_daily_stats');
    }
};
