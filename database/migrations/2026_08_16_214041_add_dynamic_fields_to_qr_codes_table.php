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
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->string('mode', 20)->default('static')->after('title');
            $table->char('public_code', 26)->nullable()->unique()->after('payload');
            $table->boolean('is_active')->default(true)->after('public_code');
            $table->timestamp('expires_at')->nullable()->index()->after('is_active');
            $table->unsignedBigInteger('scan_count')->default(0)->after('expires_at');
            $table->unsignedBigInteger('unique_scan_count')->default(0)->after('scan_count');

            $table->index(['user_id', 'mode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'mode']);
            $table->dropIndex(['expires_at']);
            $table->dropUnique(['public_code']);
            $table->dropColumn([
                'mode',
                'public_code',
                'is_active',
                'expires_at',
                'scan_count',
                'unique_scan_count',
            ]);
        });
    }
};
