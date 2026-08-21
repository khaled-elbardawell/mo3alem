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
        Schema::table('seo_settings', function (Blueprint $table) {
            $table->string('page_key', 50)->nullable()->unique()->after('id');
            $table->boolean('allow_following')->default(true)->after('allow_indexing');
            $table->string('og_title', 180)->nullable()->after('allow_following');
            $table->text('og_description')->nullable()->after('og_title');
            $table->string('og_image_alt', 180)->nullable()->after('og_image_path');
            $table->boolean('include_in_sitemap')->default(true)->after('twitter_card');
            $table->string('sitemap_change_frequency', 20)->default('weekly')->after('include_in_sitemap');
            $table->decimal('sitemap_priority', 2, 1)->default(0.9)->after('sitemap_change_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_settings', function (Blueprint $table) {
            $table->dropUnique(['page_key']);
            $table->dropColumn([
                'page_key',
                'allow_following',
                'og_title',
                'og_description',
                'og_image_alt',
                'include_in_sitemap',
                'sitemap_change_frequency',
                'sitemap_priority',
            ]);
        });
    }
};
