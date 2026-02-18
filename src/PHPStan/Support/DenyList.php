<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

final readonly class DenyList
{
    /** @var array<string, true> */
    private array $classNameLookup;

    /** @var array<string, true> */
    private array $candidateNameLookup;

    /**
     * @param array<int, string> $classNameList
     * @param array<int, string> $candidateNameList
     */
    public function __construct(
        array $classNameList = [],
        array $candidateNameList = [],
    ) {
        $this->classNameLookup     = $this->buildLookup($classNameList);
        $this->candidateNameLookup = $this->buildLookup($candidateNameList);
    }

    public function isClassNameDenied(string $className): bool
    {
        return array_key_exists(strtolower($className), $this->classNameLookup);
    }

    public function isCandidateNameDenied(string $candidateName): bool
    {
        return array_key_exists(strtolower($candidateName), $this->candidateNameLookup);
    }

    /**
     * @param array<int, string> $stringList
     *
     * @return array<string, true>
     */
    private function buildLookup(array $stringList): array
    {
        $lookup = [];

        foreach ($stringList as $value) {
            $lookup[strtolower($value)] = true;
        }

        return $lookup;
    }
}
