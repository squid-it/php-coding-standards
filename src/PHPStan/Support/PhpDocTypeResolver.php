<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

/**
 * Resolves object and iterable element types from inline PHPDoc tags.
 */
final readonly class PhpDocTypeResolver
{
    public function resolveVarTagObjectType(
        ?string $docCommentText,
        string $variableName,
        bool $allowUnnamedVarTag,
    ): ?Type {
        $typeText = $this->resolveVarTagTypeText(
            docCommentText: $docCommentText,
            variableName: $variableName,
            allowUnnamedVarTag: $allowUnnamedVarTag,
        );

        if ($typeText === null) {
            return null;
        }

        return $this->resolveObjectTypeFromPhpDocTypeText($typeText);
    }

    public function resolveNamedTagObjectType(
        ?string $docCommentText,
        string $tagName,
        string $variableName,
    ): ?Type {
        if ($docCommentText === null) {
            return null;
        }

        $typeText = $this->extractNamedTagTypeText(
            docCommentText: $docCommentText,
            tagName: $tagName,
            variableName: $variableName,
        );

        if ($typeText === null) {
            return null;
        }

        return $this->resolveObjectTypeFromPhpDocTypeText($typeText);
    }

    /**
     * @return array<int, string>
     */
    public function resolveVarTagIterableValueClassNameList(
        ?string $docCommentText,
        string $variableName,
        bool $allowUnnamedVarTag,
    ): array {
        $typeText = $this->resolveVarTagTypeText(
            docCommentText: $docCommentText,
            variableName: $variableName,
            allowUnnamedVarTag: $allowUnnamedVarTag,
        );

        if ($typeText === null) {
            return [];
        }

        return $this->extractIterableValueObjectClassNameListFromPhpDocTypeText($typeText);
    }

    private function resolveVarTagTypeText(
        ?string $docCommentText,
        string $variableName,
        bool $allowUnnamedVarTag,
    ): ?string {
        if ($docCommentText === null) {
            return null;
        }

        $typeText = $this->extractNamedTagTypeText(
            docCommentText: $docCommentText,
            tagName: 'var',
            variableName: $variableName,
        );

        if ($typeText === null && $allowUnnamedVarTag === true) {
            $typeText = $this->extractUnnamedVarTagTypeText($docCommentText);
        }

        if ($typeText === null) {
            return null;
        }

        return $typeText;
    }

    private function extractNamedTagTypeText(string $docCommentText, string $tagName, string $variableName): ?string
    {
        $lineList = preg_split('/\R/', $docCommentText);

        if ($lineList === false) {
            return null;
        }

        $tagBodyPattern  = sprintf('/^@%s\s+(.+)$/u', preg_quote($tagName, '/'));
        $variablePattern = sprintf(
            '/(?:&\s*)?(?:\.\.\.)?\$%s\b/u',
            preg_quote($variableName, '/'),
        );

        foreach ($lineList as $lineText) {
            $normalizedLine = $this->normalizeDocCommentLine($lineText);

            $tagMatchList  = [];
            $tagMatchCount = preg_match($tagBodyPattern, $normalizedLine, $tagMatchList);

            if ($tagMatchCount !== 1) {
                continue;
            }

            $tagBody = $tagMatchList[1];

            $variableMatchList  = [];
            $variableMatchCount = preg_match($variablePattern, $tagBody, $variableMatchList, PREG_OFFSET_CAPTURE);

            if ($variableMatchCount !== 1) {
                continue;
            }

            $variableOffset = $variableMatchList[0][1];
            $typeText       = trim(substr($tagBody, 0, $variableOffset));

            if ($typeText === '') {
                continue;
            }

            return $typeText;
        }

        return null;
    }

    private function extractUnnamedVarTagTypeText(string $docCommentText): ?string
    {
        $lineList = preg_split('/\R/', $docCommentText);

        if ($lineList === false) {
            return null;
        }

        foreach ($lineList as $lineText) {
            $normalizedLine = $this->normalizeDocCommentLine($lineText);

            $matchList  = [];
            $matchCount = preg_match('/^@var\s+(.+)$/u', $normalizedLine, $matchList);

            if ($matchCount !== 1) {
                continue;
            }

            $tagBody = trim($matchList[1]);

            if ($tagBody === '') {
                continue;
            }

            if (preg_match('/\$[A-Za-z_][A-Za-z0-9_]*/', $tagBody) === 1) {
                continue;
            }

            return $tagBody;
        }

        return null;
    }

    private function normalizeDocCommentLine(string $lineText): string
    {
        $normalizedLine = trim($lineText);

        if (str_starts_with($normalizedLine, '/**') === true) {
            $normalizedLine = substr($normalizedLine, 3);
        }

        if (str_ends_with($normalizedLine, '*/') === true) {
            $normalizedLine = substr($normalizedLine, 0, -2);
        }

        $normalizedLine = ltrim($normalizedLine, '*');

        return trim($normalizedLine);
    }

    private function resolveObjectTypeFromPhpDocTypeText(string $typeText): ?Type
    {
        $rawTypePartList = preg_split('/[|&]/', $typeText);

        if ($rawTypePartList === false) {
            return null;
        }

        $objectClassNameList = [];

        foreach ($rawTypePartList as $rawTypePart) {
            $typePart = trim($rawTypePart);
            $typePart = trim($typePart, " \t\n\r\0\x0B()");

            if (str_starts_with($typePart, '?') === true) {
                $typePart = substr($typePart, 1);
            }

            while (str_ends_with($typePart, '[]') === true) {
                $typePart = substr($typePart, 0, -2);
            }

            $typePart = trim($typePart);

            if ($typePart === '') {
                continue;
            }

            if ($this->isBuiltinPhpDocTypeName($typePart) === true) {
                continue;
            }

            if (preg_match('/^[A-Za-z_\\\][A-Za-z0-9_\\\]*$/', $typePart) !== 1) {
                continue;
            }

            $resolvedClassName = ltrim($typePart, '\\');

            if (in_array($resolvedClassName, $objectClassNameList, true) === false) {
                $objectClassNameList[] = $resolvedClassName;
            }
        }

        if (count($objectClassNameList) === 0) {
            return null;
        }

        $objectTypeList = [];

        foreach ($objectClassNameList as $objectClassName) {
            $objectTypeList[] = new ObjectType($objectClassName);
        }

        if (count($objectTypeList) === 1) {
            return $objectTypeList[0];
        }

        return new UnionType($objectTypeList);
    }

    /**
     * @return array<int, string>
     */
    private function extractIterableValueObjectClassNameListFromPhpDocTypeText(string $typeText): array
    {
        $typePartList        = $this->splitByTopLevelDelimiterList($typeText, ['|', '&']);
        $objectClassNameList = [];

        foreach ($typePartList as $typePart) {
            $resolvedClassNameList = $this->extractIterableValueObjectClassNameListFromTypePart($typePart);

            foreach ($resolvedClassNameList as $resolvedClassName) {
                $this->addUniqueString($objectClassNameList, $resolvedClassName);
            }
        }

        return $objectClassNameList;
    }

    /**
     * @return array<int, string>
     */
    private function extractIterableValueObjectClassNameListFromTypePart(string $typePart): array
    {
        $normalizedTypePart = trim($typePart);
        $normalizedTypePart = trim($normalizedTypePart, " \t\n\r\0\x0B()");

        if (str_starts_with($normalizedTypePart, '?') === true) {
            $normalizedTypePart = substr($normalizedTypePart, 1);
        }

        if ($normalizedTypePart === '') {
            return [];
        }

        if (str_ends_with($normalizedTypePart, '[]') === true) {
            return $this->extractIterableValueObjectClassNameListFromTypePart(
                substr($normalizedTypePart, 0, -2),
            );
        }

        $genericTypeMatch = [];
        $matchCount       = preg_match('/^([A-Za-z_\\\][A-Za-z0-9_\\\]*)\s*<(.+)>$/u', $normalizedTypePart, $genericTypeMatch);

        if ($matchCount === 1) {
            $genericArgumentList = $this->splitByTopLevelDelimiterList($genericTypeMatch[2], [',']);

            if (count($genericArgumentList) === 0) {
                return [];
            }

            $valueTypePart = $genericArgumentList[0];

            if (count($genericArgumentList) >= 2) {
                $valueTypePart = $genericArgumentList[1];
            }

            return $this->extractIterableValueObjectClassNameListFromTypePart($valueTypePart);
        }

        if (preg_match('/^[A-Za-z_\\\][A-Za-z0-9_\\\]*$/', $normalizedTypePart) !== 1) {
            return [];
        }

        if ($this->isBuiltinPhpDocTypeName($normalizedTypePart) === true) {
            return [];
        }

        return [ltrim($normalizedTypePart, '\\')];
    }

    /**
     * @param array<int, string> $delimiterList
     *
     * @return array<int, string>
     */
    private function splitByTopLevelDelimiterList(string $value, array $delimiterList): array
    {
        $partList         = [];
        $currentPart      = '';
        $angleDepth       = 0;
        $parenthesisDepth = 0;
        $bracketDepth     = 0;
        $braceDepth       = 0;
        $valueLength      = strlen($value);

        for ($characterIndex = 0; $characterIndex < $valueLength; $characterIndex++) {
            $character = $value[$characterIndex];

            if ($character === '<') {
                $angleDepth++;
                $currentPart .= $character;

                continue;
            }

            if ($character === '>') {
                if ($angleDepth > 0) {
                    $angleDepth--;
                }

                $currentPart .= $character;

                continue;
            }

            if ($character === '(') {
                $parenthesisDepth++;
                $currentPart .= $character;

                continue;
            }

            if ($character === ')') {
                if ($parenthesisDepth > 0) {
                    $parenthesisDepth--;
                }

                $currentPart .= $character;

                continue;
            }

            if ($character === '[') {
                $bracketDepth++;
                $currentPart .= $character;

                continue;
            }

            if ($character === ']') {
                if ($bracketDepth > 0) {
                    $bracketDepth--;
                }

                $currentPart .= $character;

                continue;
            }

            if ($character === '{') {
                $braceDepth++;
                $currentPart .= $character;

                continue;
            }

            if ($character === '}') {
                if ($braceDepth > 0) {
                    $braceDepth--;
                }

                $currentPart .= $character;

                continue;
            }

            if (
                $angleDepth === 0
                && $parenthesisDepth === 0
                && $bracketDepth === 0
                && $braceDepth === 0
                && in_array($character, $delimiterList, true) === true
            ) {
                $trimmedPart = trim($currentPart);

                if ($trimmedPart !== '') {
                    $partList[] = $trimmedPart;
                }

                $currentPart = '';

                continue;
            }

            $currentPart .= $character;
        }

        $trimmedPart = trim($currentPart);

        if ($trimmedPart !== '') {
            $partList[] = $trimmedPart;
        }

        return $partList;
    }

    private function isBuiltinPhpDocTypeName(string $typeName): bool
    {
        $normalizedTypeName = strtolower($typeName);

        return in_array($normalizedTypeName, [
            'array',
            'bool',
            'callable',
            'false',
            'float',
            'int',
            'iterable',
            'list',
            'mixed',
            'never',
            'null',
            'object',
            'parent',
            'resource',
            'scalar',
            'self',
            'static',
            'string',
            'true',
            'void',
        ], true);
    }

    /**
     * @param array<int, string> $stringList
     */
    private function addUniqueString(array &$stringList, string $value): void
    {
        if (in_array($value, $stringList, true) === false) {
            $stringList[] = $value;
        }
    }
}
