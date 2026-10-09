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
        Schema::create('short_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->text('destination_url');
            $table->string('slug', 50)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable()->index();
            $table->unsignedBigInteger('visit_count')->default(0);
            $table->unsignedBigInteger('unique_visit_count')->default(0);
            $table->timestamp('last_visited_at')->nullable();
            $table->unsignedBigInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'updated_at']);
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'visit_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_links');
    }
};
