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
        Schema::create('smart_board_poll_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smart_board_session_id')->constrained()->cascadeOnDelete();
            $table->string('poll_key', 64);
            $table->string('option_key', 64);
            $table->char('participant_hash', 64);
            $table->timestamps();

            $table->unique(
                ['smart_board_session_id', 'poll_key', 'participant_hash'],
                'smart_board_poll_participant_unique',
            );
            $table->index(
                ['smart_board_session_id', 'poll_key', 'option_key'],
                'smart_board_poll_counts_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_board_poll_responses');
    }
};
