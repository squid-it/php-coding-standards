<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

use PHPStan\Analyser\Scope;

final readonly class ContainingClassResolver
{
    public const array DEFAULT_ALLOWED_CREATOR_CLASS_SUFFIX_LIST = [
        'Factory',
        'Builder',
        'Provider',
    ];

    public function resolveContainingClassName(Scope $scope): ?string
    {
        $classReflection = $scope->getClassReflection();

        if ($classReflection === null) {
            return null;
        }

        return $classReflection->getName();
    }

    public function isFactoryClassName(string $className): bool
    {
        return $this->isAllowedCreatorClassName($className, ['Factory']);
    }

    /**
     * @param array<int, string> $allowedCreatorClassSuffixList
     */
    public function isAllowedCreatorClassName(
        string $className,
        array $allowedCreatorClassSuffixList = self::DEFAULT_ALLOWED_CREATOR_CLASS_SUFFIX_LIST,
    ): bool {
        $shortClassName = $this->extractShortClassName($className);

        foreach ($allowedCreatorClassSuffixList as $allowedCreatorClassSuffix) {
            if ($allowedCreatorClassSuffix === '') {
                continue;
            }

            if (str_ends_with($shortClassName, $allowedCreatorClassSuffix) === true) {
                return true;
            }
        }

        return false;
    }

    private function extractShortClassName(string $className): string
    {
        $lastNamespaceSeparatorPosition = strrpos($className, '\\');

        if ($lastNamespaceSeparatorPosition === false) {
            return $className;
        }

        return substr($className, $lastNamespaceSeparatorPosition + 1);
    }
}
