<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

final readonly class Singularizer
{
    /** @var array<int, string> */
    private const array COLLECTION_SUFFIX_LIST = [
        'List',
        'Collection',
        'Lookup',
        'ById',
        'ByKey',
    ];

    /**
     * Convert plural or collection-style names to a singular base name.
     *
     * Processing rules:
     * - Empty string stays empty.
     * - Strips one collection suffix when present: `List`, `Collection`, `Lookup`, `ById`, `ByKey`.
     * - Then de-pluralizes:
     *   - `ies` => `y`
     *   - `sses`, `shes`, `ches`, `xes`, `zes` => removes trailing `es`
     *   - trailing `s` (except `ss`) => removes trailing `s`
     *
     * Examples:
     * - `nodeList` => `node`
     * - `companies` => `company`
     * - `classes` => `class`
     * - `users` => `user`
     * - `companiesList` => `company`
     */
    public function singularize(string $word): string
    {
        if ($word === '') {
            return '';
        }

        $wordWithoutCollectionSuffix = $this->stripCollectionSuffix($word);

        return $this->depluralize($wordWithoutCollectionSuffix);
    }

    private function stripCollectionSuffix(string $word): string
    {
        foreach (self::COLLECTION_SUFFIX_LIST as $suffix) {
            if (str_ends_with($word, $suffix) === true) {
                $strippedWord = substr($word, 0, strlen($word) - strlen($suffix));

                if ($strippedWord !== '') {
                    return $strippedWord;
                }
            }
        }

        return $word;
    }

    private function depluralize(string $word): string
    {
        $lowerCaseWord = strtolower($word);

        if (str_ends_with($lowerCaseWord, 'ies') === true && strlen($word) > 4) {
            return substr($word, 0, -3) . 'y';
        }

        foreach (['sses', 'shes', 'ches', 'xes', 'zes'] as $pluralSuffix) {
            if (str_ends_with($lowerCaseWord, $pluralSuffix) === true) {
                return substr($word, 0, -2);
            }
        }

        if (str_ends_with($lowerCaseWord, 's') === true && str_ends_with($lowerCaseWord, 'ss') === false && strlen($word) > 1) {
            return substr($word, 0, -1);
        }

        return $word;
    }
}
