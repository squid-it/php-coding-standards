<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Rules\Naming;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use SquidIT\PhpCodingStandards\PHPStan\Support\Singularizer;
use SquidIT\PhpCodingStandards\PHPStan\Support\TypeCandidateResolver;
use SquidIT\PhpCodingStandards\PHPStan\Support\VariableNameMatcher;

/**
 * Enforces descriptive foreach value-variable naming.
 *
 * Naming candidates are resolved from:
 * - The singularized iterable variable name.
 * - The inferred iterable value type.
 *
 * Valid examples (assuming `$children` is inferred as `array<int, ChildNode>`):
 * - `foreach ($children as $child) {}`
 * - `foreach ($children as $childNode) {}`
 * - `foreach ($children as $firstChildNode) {}`
 *
 * Invalid example (same `array<int, ChildNode>` context):
 * - `foreach ($children as $item) {}`
 *
 * @implements Rule<Foreach_>
 */
final readonly class ForeachValueVariableNamingRule implements Rule
{
    public function __construct(
        private Singularizer $singularizer = new Singularizer(),
        private TypeCandidateResolver $typeCandidateResolver = new TypeCandidateResolver(),
        private VariableNameMatcher $variableNameMatcher = new VariableNameMatcher(),
    ) {}

    public function getNodeType(): string
    {
        return Foreach_::class;
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (($node instanceof Foreach_) === false) {
            return [];
        }

        if (($node->valueVar instanceof Variable) === false) {
            return [];
        }

        if (is_string($node->valueVar->name) === false) {
            return [];
        }

        $valueVariableName        = $node->valueVar->name;
        $allowedBaseNameListTuple = $this->resolveAllowedBaseNameList($node, $scope);
        $allowedBaseNameList      = $allowedBaseNameListTuple['allowedBaseNameList'];
        $iterableType             = $allowedBaseNameListTuple['iterableType'];

        if (count($allowedBaseNameList) === 0) {
            return [];
        }

        sort($allowedBaseNameList);

        if ($this->isValidForAnyAllowedBaseName($valueVariableName, $allowedBaseNameList) === true) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                $this->buildForeachMismatchMessage($valueVariableName, $node, $iterableType, $allowedBaseNameList),
            )
                ->identifier('squidit.naming.foreachValueVarMismatch')
                ->line($node->valueVar->getStartLine())
                ->build(),
        ];
    }

    /**
     * @param array<int, string> $allowedBaseNameList
     */
    private function isValidForAnyAllowedBaseName(string $valueVariableName, array $allowedBaseNameList): bool
    {
        foreach ($allowedBaseNameList as $allowedBaseName) {
            if ($this->variableNameMatcher->isValid($valueVariableName, $allowedBaseName) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{allowedBaseNameList: array<int, string>, iterableType: Type}
     */
    private function resolveAllowedBaseNameList(Foreach_ $foreachNode, Scope $scope): array
    {
        $allowedBaseNameList = [];

        $iterableBaseName = $this->resolveSingularizedIterableBaseName($foreachNode->expr);

        if ($iterableBaseName !== null && $iterableBaseName !== '') {
            $this->addUniqueString($allowedBaseNameList, $iterableBaseName);
        }

        $iterableType = $scope->getType($foreachNode->expr);

        if ($iterableType->isIterable()->yes() === false) {
            return [
                'allowedBaseNameList' => $allowedBaseNameList,
                'iterableType'        => $iterableType,
            ];
        }

        $iterableValueType = $iterableType->getIterableValueType();
        $typeCandidateList = $this->typeCandidateResolver->resolvePHPStanType($iterableValueType);

        foreach ($typeCandidateList as $typeCandidate) {
            $this->addUniqueString($allowedBaseNameList, $typeCandidate);
        }

        return [
            'allowedBaseNameList' => $allowedBaseNameList,
            'iterableType'        => $iterableType,
        ];
    }

    private function resolveSingularizedIterableBaseName(Node\Expr $iterableExpressionNode): ?string
    {
        if (($iterableExpressionNode instanceof Variable) === false) {
            return null;
        }

        if (is_string($iterableExpressionNode->name) === false) {
            return null;
        }

        $iterableVariableName = $iterableExpressionNode->name;

        return $this->singularizeIterableVariableName($iterableVariableName);
    }

    private function singularizeIterableVariableName(string $iterableVariableName): string
    {
        if ($this->endsWithChildren($iterableVariableName) === true) {
            $childrenSuffixLength = strlen('children');
            $prefix               = substr($iterableVariableName, 0, strlen($iterableVariableName) - $childrenSuffixLength);

            if ($prefix === '') {
                return 'child';
            }

            return $prefix . 'Child';
        }

        return $this->singularizer->singularize($iterableVariableName);
    }

    private function endsWithChildren(string $iterableVariableName): bool
    {
        return str_ends_with(strtolower($iterableVariableName), 'children');
    }

    /**
     * @param array<int, string> $allowedBaseNameList
     */
    private function buildForeachMismatchMessage(
        string $valueVariableName,
        Foreach_ $foreachNode,
        Type $iterableType,
        array $allowedBaseNameList,
    ): string {
        $allowedSuffixList = [];

        foreach ($allowedBaseNameList as $allowedBaseName) {
            $allowedSuffixList[] = ucfirst($allowedBaseName);
        }

        return sprintf(
            'Foreach value name "$%s" does not match iterable %s (inferred value type: "%s"). Allowed base names: %s. Use one of these names directly or a contextual prefix ending with: %s.',
            $valueVariableName,
            $this->describeIterableExpressionForMessage($foreachNode->expr),
            $this->describeIterableValueTypeForMessage($iterableType),
            implode(', ', $allowedBaseNameList),
            implode(', ', $allowedSuffixList),
        );
    }

    private function describeIterableExpressionForMessage(Node\Expr $iterableExpressionNode): string
    {
        if ($iterableExpressionNode instanceof Variable && is_string($iterableExpressionNode->name) === true) {
            return sprintf('"$%s"', $iterableExpressionNode->name);
        }

        return '"<expression>"';
    }

    private function describeIterableValueTypeForMessage(Type $iterableType): string
    {
        if ($iterableType->isIterable()->yes() === false) {
            return $iterableType->describe(VerbosityLevel::typeOnly());
        }

        $iterableValueType  = $iterableType->getIterableValueType();
        $shortClassNameList = [];

        foreach ($iterableValueType->getObjectClassNames() as $className) {
            $shortClassName = $this->extractShortClassName($className);

            if (in_array($shortClassName, $shortClassNameList, true) === false) {
                $shortClassNameList[] = $shortClassName;
            }
        }

        if (count($shortClassNameList) === 0) {
            return $iterableValueType->describe(VerbosityLevel::typeOnly());
        }

        sort($shortClassNameList);

        return implode('|', $shortClassNameList);
    }

    private function extractShortClassName(string $className): string
    {
        $lastNamespaceSeparatorPosition = strrpos($className, '\\');

        if ($lastNamespaceSeparatorPosition === false) {
            return $className;
        }

        return substr($className, $lastNamespaceSeparatorPosition + 1);
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
