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
        Schema::create('saved_wheels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->string('active_title', 120)->nullable();
            $table->json('names');
            $table->json('results')->nullable();
            $table->unsignedInteger('names_count')->default(0);
            $table->unsignedBigInteger('version')->default(1);
            $table->timestamp('last_opened_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'active_title']);
            $table->index(['user_id', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_wheels');
    }
};
