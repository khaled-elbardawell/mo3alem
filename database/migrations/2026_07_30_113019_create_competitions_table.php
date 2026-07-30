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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('saved_wheel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 120);
            $table->json('names');
            $table->json('results');
            $table->unsignedSmallInteger('names_count')->default(0);
            $table->unsignedInteger('results_count')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->string('status', 20)->default('draft');
            $table->boolean('sync_source_list')->default(false);
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'updated_at', 'id']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
