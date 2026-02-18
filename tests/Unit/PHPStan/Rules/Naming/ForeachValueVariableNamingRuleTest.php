<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Analyser\NodeCallbackInvoker;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\LineRuleError;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\ForeachValueVariableNamingRule;
use Throwable;

final class ForeachValueVariableNamingRuleTest extends TestCase
{
    private const string FOREACH_ERROR_MESSAGE = 'Foreach value name "$item" does not match iterable "$children" (inferred value type: "ChildNode"). Allowed base names: child, childNode. Use one of these names directly or a contextual prefix ending with: Child, ChildNode.';
    private const string EXPRESSION_ERROR      = 'Foreach value name "$element" does not match iterable "<expression>" (inferred value type: "ChildNode"). Allowed base names: childNode. Use one of these names directly or a contextual prefix ending with: ChildNode.';

    private ForeachValueVariableNamingRule $rule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule = new ForeachValueVariableNamingRule();
    }

    /**
     * @throws Throwable
     */
    public function testForeachValueNameFromIterableSingularFormSucceeds(): void
    {
        $scope       = $this->createScopeStubWithType($this->createChildNodeArrayType());
        $foreachNode = $this->createForeachNode(new Variable('children'), 'child', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testForeachValueNameMismatchFails(): void
    {
        $scope       = $this->createScopeStubWithType($this->createChildNodeArrayType());
        $foreachNode = $this->createForeachNode(new Variable('children'), 'item', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertCount(1, $errorList);

        $ruleError = $errorList[0];
        self::assertSame(self::FOREACH_ERROR_MESSAGE, $ruleError->getMessage());

        if (($ruleError instanceof IdentifierRuleError) === false) {
            self::fail('Expected IdentifierRuleError implementation.');
        }

        self::assertSame('squidit.naming.foreachValueVarMismatch', $ruleError->getIdentifier());

        if (($ruleError instanceof LineRuleError) === false) {
            self::fail('Expected LineRuleError implementation.');
        }

        self::assertSame(14, $ruleError->getLine());
    }

    /**
     * @throws Throwable
     */
    public function testForeachExpressionFallbackMismatchFails(): void
    {
        $scope = $this->createScopeStubWithType($this->createChildNodeArrayType());

        $expressionNode = new MethodCall(
            var: new Variable('this'),
            name: new Identifier('resolveChildren'),
            attributes: ['startLine' => 14],
        );
        $foreachNode = $this->createForeachNode($expressionNode, 'element', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertCount(1, $errorList);

        $ruleError = $errorList[0];
        self::assertSame(self::EXPRESSION_ERROR, $ruleError->getMessage());
    }

    private function createScopeStubWithType(Type $type): Scope&NodeCallbackInvoker
    {
        /** @var NodeCallbackInvoker&Scope&Stub $scope */
        $scope = self::createStubForIntersectionOfInterfaces([Scope::class, NodeCallbackInvoker::class]);
        $scope->method('getType')->willReturn($type);

        return $scope;
    }

    private function createChildNodeArrayType(): Type
    {
        return new ArrayType(
            keyType: new IntegerType(),
            itemType: new ObjectType('LoopValueVariableNamingFixtures\Invalid\ChildNode'),
        );
    }

    private function createForeachNode(Node\Expr $iterableExpressionNode, string $valueVariableName, int $line): Foreach_
    {
        $valueVariableNode = new Variable(
            name: $valueVariableName,
            attributes: ['startLine' => $line],
        );

        return new Foreach_(
            expr: $iterableExpressionNode,
            valueVar: $valueVariableNode,
            subNodes: ['stmts' => []],
            attributes: ['startLine' => $line],
        );
    }
}
