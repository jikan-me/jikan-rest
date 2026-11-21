<?php

namespace App\Support;

/**
 * Normalizes titles and names for alias matching.
 *
 * This is intentionally lossy: it lowercases, applies Unicode compatibility
 * normalization, replaces punctuation with spaces and collapses whitespace.
 */
final class AliasNormalizer
{
    public static function normalize(string $value): string
    {
        if (class_exists(\Normalizer::class)) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_KC) ?: $value;
        }

        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    /**
     * @return array<int, string>
     */
    public static function variants(string $value): array
    {
        $normalized = self::normalize($value);
        $compact = str_replace(' ', '', $normalized);

        return array_values(array_unique(array_filter([$normalized, $compact])));
    }
}
