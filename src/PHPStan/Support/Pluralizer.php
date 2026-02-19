<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

final readonly class Pluralizer
{
    /**
     * Convert a singular word to plural form using deterministic project rules.
     *
     * Processing rules:
     * - Empty string stays empty.
     * - If the word ends with consonant + `y`, replace `y` with `ies`.
     * - If the word ends with `s`, `x`, `z`, `ch`, or `sh`, append `es`.
     * - Otherwise append `s`.
     *
     * Examples:
     * - `company` => `companies`
     * - `class` => `classes`
     * - `box` => `boxes`
     * - `day` => `days`
     * - `user` => `users`
     */
    public function pluralize(string $word): string
    {
        if ($word === '') {
            return '';
        }

        if ($this->endsWithConsonantY($word) === true) {
            return substr($word, 0, -1) . 'ies';
        }

        if ($this->endsWithSibilantSound($word) === true) {
            return $word . 'es';
        }

        return $word . 's';
    }

    private function endsWithConsonantY(string $word): bool
    {
        if (str_ends_with($word, 'y') === false) {
            return false;
        }

        if (strlen($word) < 2) {
            return false;
        }

        $characterBeforeY = strtolower($word[strlen($word) - 2]);

        return in_array($characterBeforeY, ['a', 'e', 'i', 'o', 'u'], true) === false;
    }

    private function endsWithSibilantSound(string $word): bool
    {
        foreach (['s', 'x', 'z', 'ch', 'sh'] as $suffix) {
            if (str_ends_with($word, $suffix) === true) {
                return true;
            }
        }

        return false;
    }
}
