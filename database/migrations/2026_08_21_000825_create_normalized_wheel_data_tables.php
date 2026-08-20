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
        Schema::create('saved_wheel_names', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saved_wheel_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->unsignedSmallInteger('position');
            $table->timestamps();

            $table->unique(['saved_wheel_id', 'position']);
            $table->index(['saved_wheel_id', 'name']);
        });

        Schema::create('saved_wheel_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saved_wheel_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order');
            $table->json('payload');
            $table->timestamps();

            $table->unique(['saved_wheel_id', 'sort_order']);
        });

        Schema::create('competition_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->unsignedSmallInteger('position');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['competition_id', 'is_active', 'position']);
            $table->index(['competition_id', 'name']);
        });

        Schema::create('competition_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competition_participant_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('round');
            $table->unsignedInteger('sort_order');
            $table->string('name_snapshot', 120);
            $table->unsignedSmallInteger('position')->nullable();
            $table->timestamp('won_at', 6);
            $table->timestamps(6);

            $table->unique(['competition_id', 'round']);
            $table->index(['competition_id', 'sort_order']);
            $table->index(['competition_id', 'won_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_results');
        Schema::dropIfExists('competition_participants');
        Schema::dropIfExists('saved_wheel_results');
        Schema::dropIfExists('saved_wheel_names');
    }
};
