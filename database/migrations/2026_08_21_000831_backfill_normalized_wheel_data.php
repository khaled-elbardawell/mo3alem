<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('saved_wheels')
            ->select(['id', 'names', 'results', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->chunkById(100, function ($savedWheels): void {
                foreach ($savedWheels as $savedWheel) {
                    $names = json_decode($savedWheel->names ?: '[]', true, flags: JSON_THROW_ON_ERROR);
                    $rows = collect($names)->map(fn (string $name, int $position): array => [
                        'saved_wheel_id' => $savedWheel->id,
                        'name' => $name,
                        'position' => $position,
                        'created_at' => $savedWheel->created_at,
                        'updated_at' => $savedWheel->updated_at,
                    ])->all();

                    if ($rows !== []) {
                        DB::table('saved_wheel_names')->insert($rows);
                    }

                    $results = json_decode($savedWheel->results ?: '[]', true, flags: JSON_THROW_ON_ERROR);
                    $resultRows = collect($results)->map(fn (array $result, int $sortOrder): array => [
                        'saved_wheel_id' => $savedWheel->id,
                        'sort_order' => $sortOrder,
                        'payload' => json_encode($result, JSON_THROW_ON_ERROR),
                        'created_at' => $savedWheel->created_at,
                        'updated_at' => $savedWheel->updated_at,
                    ])->all();

                    if ($resultRows !== []) {
                        DB::table('saved_wheel_results')->insert($resultRows);
                    }
                }
            });

        DB::table('competitions')
            ->select(['id', 'names', 'results', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->chunkById(100, function ($competitions): void {
                foreach ($competitions as $competition) {
                    $names = json_decode($competition->names ?: '[]', true, flags: JSON_THROW_ON_ERROR);
                    $results = json_decode($competition->results ?: '[]', true, flags: JSON_THROW_ON_ERROR);
                    $participantRows = collect($names)->map(fn (string $name, int $position): array => [
                        'competition_id' => $competition->id,
                        'name' => $name,
                        'position' => $position,
                        'is_active' => true,
                        'created_at' => $competition->created_at,
                        'updated_at' => $competition->updated_at,
                    ])->all();

                    if ($participantRows !== []) {
                        DB::table('competition_participants')->insert($participantRows);
                    }

                    $participants = DB::table('competition_participants')
                        ->where('competition_id', $competition->id)
                        ->get(['id', 'name', 'position']);
                    $resultRows = collect($results)->map(function (array $result, int $sortOrder) use ($competition, $participants): array {
                        $position = isset($result['position']) ? (int) $result['position'] : null;
                        $name = (string) ($result['name'] ?? '');
                        $participant = $participants->first(
                            fn (object $participant): bool => $participant->name === $name
                                && (int) $participant->position === ($position === null ? -1 : $position - 1),
                        );

                        return [
                            'competition_id' => $competition->id,
                            'competition_participant_id' => $participant?->id,
                            'round' => (int) $result['round'],
                            'sort_order' => $sortOrder,
                            'name_snapshot' => $name,
                            'position' => $position,
                            'won_at' => Carbon::parse($result['date'])
                                ->setTimezone(config('app.timezone'))
                                ->format('Y-m-d H:i:s.u'),
                            'created_at' => $competition->created_at,
                            'updated_at' => $competition->updated_at,
                        ];
                    })->all();

                    if ($resultRows !== []) {
                        DB::table('competition_results')->insert($resultRows);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The normalized rows remain available until their tables are rolled back.
    }
};
