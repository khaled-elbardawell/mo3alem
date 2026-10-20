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
        Schema::create('typing_passages', function (Blueprint $table) {
            $table->id();
            $table->string('seed_key', 24)->nullable()->unique();
            $table->string('language', 2);
            $table->text('content');
            $table->char('content_hash', 64)->unique();
            $table->unsignedSmallInteger('word_count');
            $table->unsignedSmallInteger('expected_seconds');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['language', 'is_active', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('typing_passages');
    }
};
