<?php

namespace Database\Seeders;

use App\Models\TypingPassage;
use App\Services\TypingPassageCalculator;
use App\TypingPassageLanguage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use JsonException;

class TypingPassageSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(TypingPassageCalculator $calculator): void
    {
        $timestamp = now();

        foreach (TypingPassageLanguage::cases() as $language) {
            $passages = $this->passages($language);

            collect($passages)
                ->map(function (string $content, int $index) use ($calculator, $language, $timestamp): array {
                    return [
                        'seed_key' => sprintf('%s-%04d', $language->value, $index + 1),
                        'language' => $language->value,
                        ...$calculator->attributes($content),
                        'is_active' => true,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                })
                ->chunk(200)
                ->each(fn ($chunk) => TypingPassage::query()->insertOrIgnore($chunk->all()));
        }
    }

    /** @return array<int, string> */
    private function passages(TypingPassageLanguage $language): array
    {
        $path = database_path("data/typing-passages-{$language->value}.json");

        try {
            $passages = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new JsonException("Unable to decode typing passage data at {$path}.", previous: $exception);
        }

        if (! is_array($passages) || count($passages) !== 1200) {
            throw new JsonException("Typing passage data at {$path} must contain exactly 1200 passages.");
        }

        return array_values($passages);
    }
}
