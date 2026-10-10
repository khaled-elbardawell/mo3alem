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
        Schema::create('smart_board_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smart_board_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('public_code', 6)->unique();
            $table->char('owner_token_hash', 64);
            $table->string('status', 20)->default('active')->index();
            $table->string('poll_key', 64)->nullable();
            $table->json('poll_snapshot')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index(['public_code', 'status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_board_sessions');
    }
};
