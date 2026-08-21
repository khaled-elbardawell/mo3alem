<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existingHome = DB::table('seo_settings')->orderBy('id')->first();

        if ($existingHome) {
            DB::table('seo_settings')
                ->where('id', $existingHome->id)
                ->update(['page_key' => 'home']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('seo_settings')
            ->where('page_key', 'home')
            ->update(['page_key' => null]);
    }
};
