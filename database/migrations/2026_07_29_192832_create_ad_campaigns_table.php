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
        Schema::create('ad_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('image_path');
            $table->string('target_url', 2048);
            $table->string('alt_text', 180);
            $table->string('placement')->index();
            $table->string('status')->default('draft')->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->unsignedSmallInteger('weight')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['placement', 'status', 'starts_at', 'ends_at'], 'ad_campaign_eligibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_campaigns');
    }
};
