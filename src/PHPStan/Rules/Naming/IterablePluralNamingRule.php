<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Rules\Naming;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use SquidIT\PhpCodingStandards\PHPStan\Support\PhpDocTypeResolver;
use SquidIT\PhpCodingStandards\PHPStan\Support\TypeCandidateResolver;
use SquidIT\PhpCodingStandards\PHPStan\Support\TypeMessageDescriber;
use SquidIT\PhpCodingStandards\PHPStan\Support\VariableNameMatcher;

/**
 * Enforces `List`-suffixed naming for object iterables.
 *
 * This rule checks assignment targets where the inferred expression type is iterable and each element
 * type resolves to object-based naming candidates.
 *
 * Inline assignment `@var` narrowing is respected for iterable value types.
 *
 * Valid examples:
 * - `$nodeList = [$node];`
 * - `$activeNodeList = [$node];`
 * - `$primaryNodeList = ['id' => $node];`
 *
 * Invalid examples:
 * - `$nodes = [$node];` (must use `*List` naming)
 * - `$itemList = [$node];` (does not match inferred element type)
 * - `$nodeMap = ['id' => $node];` (`Map` segment is forbidden, must use `*List`)
 *
 * @implements Rule<Expression>
 */
final readonly class IterablePluralNamingRule implements Rule
{
    /** @var array<int, string> */
    private const array COLLECTION_SUFFIX_LIST = [
        'List',
    ];

    public function __construct(
        private TypeCandidateResolver $typeCandidateResolver = new TypeCandidateResolver(),
        private VariableNameMatcher $variableNameMatcher = new VariableNameMatcher(),
        private TypeMessageDescriber $typeMessageDescriber = new TypeMessageDescriber(),
        private PhpDocTypeResolver $phpDocTypeResolver = new PhpDocTypeResolver(),
    ) {}

    public function getNodeType(): string
    {
        return Expression::class;
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (($node instanceof Expression) === false) {
            return [];
        }

        if (($node->expr instanceof Assign) === false) {
            return [];
        }

        $assignmentNode       = $node->expr;
        $assignmentTargetName = $this->extractAssignmentTargetName($assignmentNode);

        if ($assignmentTargetName === null) {
            return [];
        }

        $assignedType = $scope->getType($assignmentNode->expr);

        if ($assignedType->isIterable()->yes() === false) {
            return [];
        }

        $iterableValueClassNameList = $this->phpDocTypeResolver->resolveVarTagIterableValueClassNameList(
            docCommentText: $node->getDocComment()?->getText(),
            variableName: $assignmentTargetName,
            allowUnnamedVarTag: true,
        );

        if (count($iterableValueClassNameList) > 0) {
            $docCommentCandidateBaseNameList = $this->resolveCandidateBaseNameListFromClassNameList($iterableValueClassNameList);

            if (count($docCommentCandidateBaseNameList) > 0) {
                return $this->buildRuleErrorList(
                    name: $assignmentTargetName,
                    type: $assignedType,
                    line: $assignmentNode->getStartLine(),
                    candidateBaseNameList: $docCommentCandidateBaseNameList,
                    elementTypeDescription: implode('|', $iterableValueClassNameList),
                );
            }
        }

        $candidateBaseNameList = $this->resolveIterableCandidateBaseNameList($assignedType);

        if (count($candidateBaseNameList) === 0) {
            return [];
        }

        return $this->buildRuleErrorList(
            name: $assignmentTargetName,
            type: $assignedType,
            line: $assignmentNode->getStartLine(),
            candidateBaseNameList: $candidateBaseNameList,
        );
    }

    /**
     * @param array<int, string> $candidateBaseNameList
     *
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    private function buildRuleErrorList(
        string $name,
        Type $type,
        int $line,
        array $candidateBaseNameList,
        ?string $elementTypeDescription = null,
    ): array {
        sort($candidateBaseNameList);

        $errorList = [];

        if ($this->containsForbiddenMapSegment($name) === true) {
            $errorList[] = RuleErrorBuilder::message($this->buildMapForbiddenMessage($name))
                ->identifier('squidit.naming.mapForbidden')
                ->line($line)
                ->build();
        }

        if ($this->isValidForAnyIterableCandidateBaseName($name, $candidateBaseNameList) === false) {
            $errorList[] = RuleErrorBuilder::message(
                $this->buildIterablePluralMismatchMessage($name, $type, $candidateBaseNameList, $elementTypeDescription),
            )
                ->identifier('squidit.naming.iterablePluralMismatch')
                ->line($line)
                ->build();
        }

        return $errorList;
    }

    /**
     * @param array<int, string> $classNameList
     *
     * @return array<int, string>
     */
    private function resolveCandidateBaseNameListFromClassNameList(array $classNameList): array
    {
        $candidateBaseNameList = [];

        foreach ($classNameList as $className) {
            $resolvedCandidateBaseNameList = $this->typeCandidateResolver->resolvePHPStanType(
                new ObjectType($className),
            );

            foreach ($resolvedCandidateBaseNameList as $resolvedCandidateBaseName) {
                $this->addUniqueString($candidateBaseNameList, $resolvedCandidateBaseName);
            }
        }

        return $candidateBaseNameList;
    }

    private function containsForbiddenMapSegment(string $name): bool
    {
        $segmentList = preg_split('/(?=[A-Z])/', $name);

        if (is_array($segmentList) === false) {
            return false;
        }

        foreach ($segmentList as $segment) {
            if (strtolower($segment) === 'map') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, string> $candidateBaseNameList
     */
    private function isValidForAnyIterableCandidateBaseName(string $name, array $candidateBaseNameList): bool
    {
        foreach ($candidateBaseNameList as $candidateBaseName) {
            foreach (self::COLLECTION_SUFFIX_LIST as $collectionSuffix) {
                if (
                    $this->variableNameMatcher->isValid(
                        $name,
                        $candidateBaseName . $collectionSuffix,
                    ) === true
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function resolveIterableCandidateBaseNameList(Type $type): array
    {
        if ($type->isIterable()->yes() === false) {
            return [];
        }

        $iterableValueType = $type->getIterableValueType();

        return $this->typeCandidateResolver->resolvePHPStanType($iterableValueType);
    }

    /**
     * @param array<int, string> $candidateBaseNameList
     */
    private function buildIterablePluralMismatchMessage(
        string $name,
        Type $type,
        array $candidateBaseNameList,
        ?string $elementTypeDescription = null,
    ): string {
        $typeDescription = $elementTypeDescription ?? $this->typeMessageDescriber->describeIterableValueType($type);

        return sprintf(
            'Iterable name "$%s" does not match inferred iterable element type "%s". Allowed base names: %s. Use one of these names directly or a contextual prefix ending with: %s.',
            $name,
            $typeDescription,
            implode(', ', $candidateBaseNameList),
            implode(', ', $this->buildAllowedListSuffixNameList($candidateBaseNameList)),
        );
    }

    private function buildMapForbiddenMessage(string $name): string
    {
        return sprintf(
            'Iterable name "$%s" contains forbidden segment "Map". Use "*List" naming instead.',
            $name,
        );
    }

    /**
     * @param array<int, string> $candidateBaseNameList
     *
     * @return array<int, string>
     */
    private function buildAllowedListSuffixNameList(array $candidateBaseNameList): array
    {
        $allowedNameList = [];

        foreach ($candidateBaseNameList as $candidateBaseName) {
            foreach (self::COLLECTION_SUFFIX_LIST as $collectionSuffix) {
                $allowedNameList[] = $candidateBaseName . $collectionSuffix;
            }
        }

        return $allowedNameList;
    }

    private function extractAssignmentTargetName(Assign $assignNode): ?string
    {
        if ($assignNode->var instanceof Variable) {
            if (is_string($assignNode->var->name) === false) {
                return null;
            }

            return $assignNode->var->name;
        }

        if (($assignNode->var instanceof PropertyFetch) === false) {
            return null;
        }

        if (($assignNode->var->var instanceof Variable) === false) {
            return null;
        }

        if ($assignNode->var->var->name !== 'this') {
            return null;
        }

        if (($assignNode->var->name instanceof Identifier) === false) {
            return null;
        }

        return $assignNode->var->name->toString();
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
