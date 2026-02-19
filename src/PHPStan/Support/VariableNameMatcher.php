<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

final readonly class VariableNameMatcher
{
    /**
     * Validate whether a variable/property name matches an allowed base name.
     *
     * Matching rules:
     * - Exact match is valid.
     * - Prefixed names are valid when they end with `ucfirst($baseName)`.
     * - Empty base names are never valid.
     *
     * Examples:
     * - `channel` vs `channel` => valid
     * - `readChannel` vs `channel` => valid
     * - `item` vs `channel` => invalid
     */
    public function isValid(string $variableName, string $baseName): bool
    {
        if ($variableName === $baseName) {
            return true;
        }

        if ($baseName === '') {
            return false;
        }

        $capitalizedBaseName = ucfirst($baseName);

        return str_ends_with($variableName, $capitalizedBaseName);
    }

    /**
     * Determine if an interface bare-name notice should be reported.
     *
     * This method compares the current variable/property name against an interface-derived
     * base name and reports when it is used as a bare name without context prefix.
     *
     * Examples:
     * - `channel` + base `channel` => report notice
     * - `readChannel` + base `channel` => no notice
     */
    public function shouldReportInterfaceBareNameNotice(string $variableName, string $baseName): bool
    {
        return $variableName === $baseName;
    }
}
