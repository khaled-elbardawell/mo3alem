<?php

namespace App\Services;

class TypingPassageCalculator
{
    public const WORDS_PER_MINUTE = 30;

    public function normalize(string $content): string
    {
        $normalized = class_exists(\Normalizer::class)
            ? (\Normalizer::normalize($content, \Normalizer::FORM_C) ?: $content)
            : $content;

        return trim((string) preg_replace('/\s+/u', ' ', $normalized));
    }

    public function wordCount(string $content): int
    {
        $normalized = $this->normalize($content);

        if ($normalized === '') {
            return 0;
        }

        return count(preg_split('/\s+/u', $normalized) ?: []);
    }

    public function expectedSeconds(string $content): int
    {
        $standardWords = mb_strlen($this->normalize($content)) / 5;

        return max(10, min(600, (int) ceil(($standardWords / self::WORDS_PER_MINUTE) * 60)));
    }

    /** @return array{content: string, content_hash: string, word_count: int, expected_seconds: int} */
    public function attributes(string $content, ?int $expectedSeconds = null): array
    {
        $normalized = $this->normalize($content);

        return [
            'content' => $normalized,
            'content_hash' => hash('sha256', $normalized),
            'word_count' => $this->wordCount($normalized),
            'expected_seconds' => $expectedSeconds ?? $this->expectedSeconds($normalized),
        ];
    }
}
