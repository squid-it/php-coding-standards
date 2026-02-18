<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Rules\Naming;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Enforces camelCase backed string values for enums.
 *
 * This rule checks only string-backed enums and only literal string case values.
 *
 * Valid examples:
 * - `case FooBar = 'fooBar';`
 * - `case FooBar = 'foo_bar';` when a `to*()` method references the same literal.
 *
 * Invalid example:
 * - `case FooBar = 'foo_bar';` without a matching `to*()` literal reference.
 *
 * @implements Rule<Enum_>
 */
final readonly class EnumBackedValueCamelCaseRule implements Rule
{
    public function getNodeType(): string
    {
        return Enum_::class;
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (($node instanceof Enum_) === false) {
            return [];
        }

        if ($this->isStringBackedEnum($node) === false) {
            return [];
        }

        $toMethodLiteralLookup = $this->resolveToMethodLiteralLookup($node);
        $errorList             = [];

        foreach ($node->stmts as $statementNode) {
            if (($statementNode instanceof EnumCase) === false) {
                continue;
            }

            if (($statementNode->expr instanceof String_) === false) {
                continue;
            }

            $backedValue = $statementNode->expr->value;

            if ($this->isCamelCase($backedValue) === true) {
                continue;
            }

            if (array_key_exists($backedValue, $toMethodLiteralLookup) === true) {
                continue;
            }

            $errorList[] = RuleErrorBuilder::message(
                $this->buildBackedValueCamelCaseMessage($node, $statementNode, $backedValue),
            )
                ->identifier('squidit.naming.enumBackedValueCamelCase')
                ->line($statementNode->expr->getStartLine())
                ->build();
        }

        return $errorList;
    }

    private function isStringBackedEnum(Enum_ $enumNode): bool
    {
        if ($enumNode->scalarType === null) {
            return false;
        }

        if (($enumNode->scalarType instanceof Identifier) === false) {
            return false;
        }

        return strtolower($enumNode->scalarType->toString()) === 'string';
    }

    /**
     * @return array<string, true>
     */
    private function resolveToMethodLiteralLookup(Enum_ $enumNode): array
    {
        $toMethodLiteralLookup = [];

        foreach ($enumNode->stmts as $statementNode) {
            if (($statementNode instanceof ClassMethod) === false) {
                continue;
            }

            if ($this->isToMethodName($statementNode->name->toString()) === false) {
                continue;
            }

            if ($statementNode->stmts === null) {
                continue;
            }

            foreach ($statementNode->stmts as $methodStatementNode) {
                $this->collectReturnedStringLiteralLookup($methodStatementNode, $toMethodLiteralLookup);
            }
        }

        return $toMethodLiteralLookup;
    }

    private function isToMethodName(string $methodName): bool
    {
        if (str_starts_with($methodName, 'to') === false) {
            return false;
        }

        if (strlen($methodName) <= 2) {
            return false;
        }

        return ctype_upper($methodName[2]);
    }

    /**
     * @param array<string, true> $stringLiteralLookup
     */
    private function collectReturnedStringLiteralLookup(Node $node, array &$stringLiteralLookup): void
    {
        if ($node instanceof Return_ && $node->expr !== null) {
            $this->collectStringLiteralLookup($node->expr, $stringLiteralLookup);
        }

        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->{$subNodeName};

            if ($subNode instanceof Node) {
                $this->collectReturnedStringLiteralLookup($subNode, $stringLiteralLookup);

                continue;
            }

            if (is_array($subNode) === false) {
                continue;
            }

            foreach ($subNode as $subNodeItem) {
                if (($subNodeItem instanceof Node) === false) {
                    continue;
                }

                $this->collectReturnedStringLiteralLookup($subNodeItem, $stringLiteralLookup);
            }
        }
    }

    /**
     * @param array<string, true> $stringLiteralLookup
     */
    private function collectStringLiteralLookup(Node $node, array &$stringLiteralLookup): void
    {
        if ($node instanceof String_) {
            $stringLiteralLookup[$node->value] = true;
        }

        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->{$subNodeName};

            if ($subNode instanceof Node) {
                $this->collectStringLiteralLookup($subNode, $stringLiteralLookup);

                continue;
            }

            if (is_array($subNode) === false) {
                continue;
            }

            foreach ($subNode as $subNodeItem) {
                if (($subNodeItem instanceof Node) === false) {
                    continue;
                }

                $this->collectStringLiteralLookup($subNodeItem, $stringLiteralLookup);
            }
        }
    }

    private function isCamelCase(string $value): bool
    {
        return (bool) preg_match('/^[a-z][a-zA-Z0-9]*$/', $value);
    }

    private function buildBackedValueCamelCaseMessage(Enum_ $enumNode, EnumCase $enumCaseNode, string $backedValue): string
    {
        $enumName = $enumNode->name === null
            ? '<anonymous-enum>'
            : $enumNode->name->toString();

        return sprintf(
            'Backed enum value "%s" on case "%s::%s" must be camelCase unless the same literal is referenced by a to*() method.',
            $backedValue,
            $enumName,
            $enumCaseNode->name->toString(),
        );
    }
}
