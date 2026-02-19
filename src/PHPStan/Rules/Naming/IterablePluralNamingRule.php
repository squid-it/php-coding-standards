<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Rules\Naming;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\Type;
use SquidIT\PhpCodingStandards\PHPStan\Support\Pluralizer;
use SquidIT\PhpCodingStandards\PHPStan\Support\TypeCandidateResolver;
use SquidIT\PhpCodingStandards\PHPStan\Support\TypeMessageDescriber;
use SquidIT\PhpCodingStandards\PHPStan\Support\VariableNameMatcher;

/**
 * Enforces plural or collection-style naming for object iterables.
 *
 * This rule checks assignment targets where the inferred expression type is iterable and each element
 * type resolves to object-based naming candidates.
 *
 * Valid examples:
 * - `$nodes = [$node];`
 * - `$nodeList = [$node];`
 * - `$activeNodeList = [$node];`
 * - `$nodeById = ['id' => $node];`
 *
 * Invalid examples:
 * - `$itemList = [$node];` (does not match inferred element type)
 * - `$nodeMap = ['id' => $node];` (`Map` segment is forbidden)
 *
 * @implements Rule<Node>
 */
final readonly class IterablePluralNamingRule implements Rule
{
    /** @var array<int, string> */
    private const array COLLECTION_SUFFIX_LIST = [
        'List',
        'Collection',
        'Lookup',
        'ById',
        'ByKey',
    ];

    public function __construct(
        private TypeCandidateResolver $typeCandidateResolver = new TypeCandidateResolver(),
        private VariableNameMatcher $variableNameMatcher = new VariableNameMatcher(),
        private Pluralizer $pluralizer = new Pluralizer(),
        private TypeMessageDescriber $typeMessageDescriber = new TypeMessageDescriber(),
    ) {}

    public function getNodeType(): string
    {
        return Assign::class;
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (($node instanceof Assign) === false) {
            return [];
        }

        $assignmentTargetName = $this->extractAssignmentTargetName($node);

        if ($assignmentTargetName === null) {
            return [];
        }

        $assignedType = $scope->getType($node->expr);

        return $this->buildRuleErrorList($assignmentTargetName, $assignedType, $node->getStartLine());
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    private function buildRuleErrorList(string $name, Type $type, int $line): array
    {
        $candidateBaseNameList = $this->resolveIterableCandidateBaseNameList($type);

        if (count($candidateBaseNameList) === 0) {
            return [];
        }

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
                $this->buildIterablePluralMismatchMessage($name, $type, $candidateBaseNameList),
            )
                ->identifier('squidit.naming.iterablePluralMismatch')
                ->line($line)
                ->build();
        }

        return $errorList;
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
            $pluralBaseName = $this->pluralizer->pluralize($candidateBaseName);

            if ($this->variableNameMatcher->isValid($name, $pluralBaseName) === true) {
                return true;
            }

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
    private function buildIterablePluralMismatchMessage(string $name, Type $type, array $candidateBaseNameList): string
    {
        return sprintf(
            'Iterable name "$%s" does not match inferred iterable element type "%s". Allowed base names: %s. Use plural form or collection suffixes: List, Collection, Lookup, ById, ByKey.',
            $name,
            $this->typeMessageDescriber->describeIterableValueType($type),
            implode(', ', $candidateBaseNameList),
        );
    }

    private function buildMapForbiddenMessage(string $name): string
    {
        return sprintf(
            'Iterable name "$%s" contains forbidden segment "Map". Use "List", "Collection", "Lookup", "ById", or "ByKey" naming instead.',
            $name,
        );
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
}
