<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('saved_wheels', function (Blueprint $table) {
            $table->dropColumn(['names', 'results']);
        });

        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn(['names', 'results']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saved_wheels', function (Blueprint $table) {
            $table->json('names')->nullable();
            $table->json('results')->nullable();
        });

        Schema::table('competitions', function (Blueprint $table) {
            $table->json('names')->nullable();
            $table->json('results')->nullable();
        });

        DB::table('saved_wheels')->orderBy('id')->eachById(function (object $savedWheel): void {
            $names = DB::table('saved_wheel_names')
                ->where('saved_wheel_id', $savedWheel->id)
                ->orderBy('position')
                ->pluck('name')
                ->all();
            $results = DB::table('saved_wheel_results')
                ->where('saved_wheel_id', $savedWheel->id)
                ->orderBy('sort_order')
                ->pluck('payload')
                ->map(fn (string $payload): array => json_decode($payload, true, flags: JSON_THROW_ON_ERROR))
                ->all();

            DB::table('saved_wheels')->where('id', $savedWheel->id)->update([
                'names' => json_encode($names, JSON_THROW_ON_ERROR),
                'results' => json_encode($results, JSON_THROW_ON_ERROR),
            ]);
        });

        DB::table('competitions')->orderBy('id')->eachById(function (object $competition): void {
            $names = DB::table('competition_participants')
                ->where('competition_id', $competition->id)
                ->where('is_active', true)
                ->orderBy('position')
                ->pluck('name')
                ->all();
            $results = DB::table('competition_results')
                ->where('competition_id', $competition->id)
                ->orderBy('sort_order')
                ->get()
                ->map(fn (object $result): array => [
                    'round' => (int) $result->round,
                    'name' => $result->name_snapshot,
                    'date' => Carbon::parse($result->won_at)->toISOString(),
                    'position' => $result->position === null ? null : (int) $result->position,
                ])->all();

            DB::table('competitions')->where('id', $competition->id)->update([
                'names' => json_encode($names, JSON_THROW_ON_ERROR),
                'results' => json_encode($results, JSON_THROW_ON_ERROR),
            ]);
        });
    }
};
