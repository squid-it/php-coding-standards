<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

final readonly class NameNormalizer
{
    /** @var array<int, string> */
    private const array MANDATORY_STRIP_PREFIX_LIST = [
        'Abstract',
    ];

    /** @var array<int, string> */
    private const array MANDATORY_STRIP_SUFFIX_LIST = [
        'Interface',
        'Abstract',
        'Trait',
    ];

    /** @var array<int, string> */
    private const array OPTIONAL_STRIP_SUFFIX_LIST = [
        'Dto',
        'Vo',
        'Entity',
    ];

    /** @var array<int, string> */
    private const array NEVER_STRIP_SUFFIX_LIST = [
        'Factory',
        'Collection',
    ];

    /**
     * Resolve deterministic variable-name candidates from a class name.
     *
     * Processing rules:
     * - Uses only the short class name (namespace is ignored).
     * - Normalizes the base to camelCase.
     * - Mandatory prefix `Abstract` (as a camelCase word boundary) is stripped first; only the stripped form is returned.
     * - Mandatory suffixes `Interface`, `Abstract`, `Trait` are stripped and only the stripped form is returned.
     * - Optional suffixes `Dto`, `Vo`, `Entity` keep both unstripped and stripped forms.
     * - Never-strip suffixes `Factory`, `Collection` keep only the unstripped form.
     *
     * Examples:
     * - `AbstractServiceMessage` => `['serviceMessage']`
     * - `AbstractFooInterface` => `['foo']`
     * - `ChannelInterface` => `['channel']`
     * - `UserDto` => `['userDto', 'user']`
     * - `OrderEntity` => `['orderEntity', 'order']`
     * - `UserFactory` => `['userFactory']`
     * - `NodeCollection` => `['nodeCollection']`
     *
     * @return array<int, string>
     */
    public function normalize(string $className): array
    {
        $shortClassName = $this->extractShortClassName($className);

        $matchingPrefix = $this->findMatchingPrefix($shortClassName, self::MANDATORY_STRIP_PREFIX_LIST);

        if ($matchingPrefix !== null) {
            $prefixStrippedName = $this->stripPrefix($shortClassName, $matchingPrefix);

            if ($prefixStrippedName !== '') {
                $shortClassName = $prefixStrippedName;
            }
        }

        $camelCaseShortClassName = $this->toCamelCase($shortClassName);

        if ($this->findMatchingSuffix($shortClassName, self::NEVER_STRIP_SUFFIX_LIST) !== null) {
            return [$camelCaseShortClassName];
        }

        $mandatorySuffix = $this->findMatchingSuffix($shortClassName, self::MANDATORY_STRIP_SUFFIX_LIST);

        if ($mandatorySuffix !== null) {
            $strippedClassName = $this->stripSuffix($shortClassName, $mandatorySuffix);

            if ($strippedClassName === '') {
                return [$camelCaseShortClassName];
            }

            return [$this->toCamelCase($strippedClassName)];
        }

        $optionalSuffix = $this->findMatchingSuffix($shortClassName, self::OPTIONAL_STRIP_SUFFIX_LIST);

        if ($optionalSuffix !== null) {
            $strippedClassName = $this->stripSuffix($shortClassName, $optionalSuffix);

            if ($strippedClassName === '') {
                return [$camelCaseShortClassName];
            }

            return array_values(
                array_unique([
                    $camelCaseShortClassName,
                    $this->toCamelCase($strippedClassName),
                ]),
            );
        }

        return [$camelCaseShortClassName];
    }

    private function extractShortClassName(string $className): string
    {
        $lastNamespaceSeparatorPosition = strrpos($className, '\\');

        if ($lastNamespaceSeparatorPosition === false) {
            return $className;
        }

        return substr($className, $lastNamespaceSeparatorPosition + 1);
    }

    private function stripSuffix(string $value, string $suffix): string
    {
        return substr($value, 0, strlen($value) - strlen($suffix));
    }

    private function stripPrefix(string $value, string $prefix): string
    {
        return substr($value, strlen($prefix));
    }

    /**
     * @param array<int, string> $prefixList
     */
    private function findMatchingPrefix(string $value, array $prefixList): ?string
    {
        foreach ($prefixList as $prefix) {
            if (str_starts_with($value, $prefix) === false) {
                continue;
            }

            $remaining = substr($value, strlen($prefix));

            if ($remaining === '' || ctype_upper($remaining[0]) === true) {
                return $prefix;
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $suffixList
     */
    private function findMatchingSuffix(string $value, array $suffixList): ?string
    {
        foreach ($suffixList as $suffix) {
            if (str_ends_with($value, $suffix) === true) {
                return $suffix;
            }
        }

        return null;
    }

    private function toCamelCase(string $className): string
    {
        if ($className === '') {
            return '';
        }

        $matches = [];
        preg_match_all('/[A-Z]+(?=[A-Z][a-z]|[0-9]|$)|[A-Z]?[a-z]+|[0-9]+/', $className, $matches);
        /** @var array<int, string> $partList */
        $partList = $matches[0];

        if (count($partList) === 0) {
            return lcfirst($className);
        }

        $camelCasePartList = [];

        foreach ($partList as $index => $part) {
            $partLowerCase = strtolower($part);

            if ($index === 0) {
                $camelCasePartList[] = $partLowerCase;

                continue;
            }

            if (ctype_digit($part) === true) {
                $camelCasePartList[] = $part;

                continue;
            }

            $camelCasePartList[] = ucfirst($partLowerCase);
        }

        return implode('', $camelCasePartList);
    }
}
