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
use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\ForeachValueVariableNamingRule;
use Throwable;

final class ForeachValueVariableNamingRuleTest extends TestCase
{
    private const string FOREACH_ERROR_MESSAGE              = 'Foreach value name "$item" does not match iterable "$children" (inferred value type: "ChildNode"). Allowed base names: child, childNode, childValue. Use one of these names directly or a contextual prefix ending with: Child, ChildNode, ChildValue.';
    private const string EXPRESSION_ERROR                   = 'Foreach value name "$element" does not match iterable "<expression>" (inferred value type: "ChildNode"). Allowed base names: childNode. Use one of these names directly or a contextual prefix ending with: ChildNode.';
    private const string FOREACH_INTERSECTION_ERROR_MESSAGE = 'Foreach value name "$item" does not match iterable "$children" (inferred value type: "ChildNode|NodeInterface"). Allowed base names: child, childNode, childValue, node. Use one of these names directly or a contextual prefix ending with: Child, ChildNode, ChildValue, Node.';

    private ForeachValueVariableNamingRule $rule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule = new ForeachValueVariableNamingRule();
    }

    public function testGetNodeTypeReturnsForeachClassSucceeds(): void
    {
        self::assertSame(Foreach_::class, $this->rule->getNodeType());
    }

    /**
     * @throws Throwable
     */
    public function testForeachWithNonVariableValueReturnsNoErrorsSucceeds(): void
    {
        $scope = $this->createScopeStubWithType($this->createStringArrayType());

        $foreachNode = $this->createForeachNodeWithValueExpression(
            iterableExpressionNode: new Variable('settings'),
            valueExpressionNode: new MethodCall(
                var: new Variable('this'),
                name: new Identifier('resolveValue'),
                attributes: ['startLine' => 14],
            ),
            line: 14,
        );

        $errorList = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testForeachWithDynamicValueVariableNameReturnsNoErrorsSucceeds(): void
    {
        $scope = $this->createScopeStubWithType($this->createStringArrayType());

        $foreachNode = $this->createForeachNodeWithValueExpression(
            iterableExpressionNode: new Variable('settings'),
            valueExpressionNode: new Variable(
                name: new Variable('dynamic'),
                attributes: ['startLine' => 14],
            ),
            line: 14,
        );

        $errorList = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
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
    public function testForeachValueNameWithValueSuffixFromIterableVariableSucceeds(): void
    {
        $scope       = $this->createScopeStubWithType($this->createStringArrayType());
        $foreachNode = $this->createForeachNode(new Variable('settings'), 'settingValue', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testForeachKeyToValuePairNamingSucceeds(): void
    {
        $scope       = $this->createScopeStubWithType($this->createStringArrayType());
        $foreachNode = $this->createForeachNode(new Variable('settings'), 'settingValue', 14, 'settingKey');
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testForeachIndexToValuePairNamingSucceeds(): void
    {
        $scope       = $this->createScopeStubWithType($this->createStringArrayType());
        $foreachNode = $this->createForeachNode(new Variable('settings'), 'settingValue', 14, 'settingIndex');
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testForeachExpressionWithKeyToValuePairNamingSucceeds(): void
    {
        $scope = $this->createScopeStubWithType($this->createStringArrayType());

        $expressionNode = new MethodCall(
            var: new Variable('this'),
            name: new Identifier('getSettings'),
            attributes: ['startLine' => 14],
        );
        $foreachNode = $this->createForeachNode($expressionNode, 'settingValue', 14, 'settingKey');
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testForeachWithUnsupportedKeySuffixSkipsPairShortcutSucceeds(): void
    {
        $scope       = $this->createScopeStubWithType($this->createStringArrayType());
        $foreachNode = $this->createForeachNode(new Variable('settings'), 'settingValue', 14, 'settingId');
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testForeachWithEmptyKeyStemSkipsPairShortcutSucceeds(): void
    {
        $scope       = $this->createScopeStubWithType($this->createStringArrayType());
        $foreachNode = $this->createForeachNode(new Variable('settings'), 'settingValue', 14, 'Key');
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testForeachWithEmptyValueStemFails(): void
    {
        $scope       = $this->createScopeStubWithType($this->createStringArrayType());
        $foreachNode = $this->createForeachNode(new Variable('settings'), 'Value', 14, 'settingKey');
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertCount(1, $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testForeachChildrenSuffixWithPrefixSucceeds(): void
    {
        $scope       = $this->createScopeStubWithType($this->createStringArrayType());
        $foreachNode = $this->createForeachNode(new Variable('activeChildren'), 'activeChild', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testForeachDynamicIterableVariableNameSkipsSingularizationFallbackSucceeds(): void
    {
        $scope = $this->createScopeStubWithType($this->createStringArrayType());

        $foreachNode = $this->createForeachNode(
            iterableExpressionNode: new Variable(
                name: new Variable('iterableName'),
                attributes: ['startLine' => 14],
            ),
            valueVariableName: 'settingValue',
            line: 14,
        );
        $errorList = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testForeachExpressionWithNonIterableTypeReturnsNoErrorsSucceeds(): void
    {
        $scope = $this->createScopeStubWithType(new StringType());

        $expressionNode = new MethodCall(
            var: new Variable('this'),
            name: new Identifier('resolveSettings'),
            attributes: ['startLine' => 14],
        );
        $foreachNode = $this->createForeachNode($expressionNode, 'item', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testForeachVariableWithNonIterableTypeMismatchUsesTypeDescriptionFails(): void
    {
        $scope       = $this->createScopeStubWithType(new StringType());
        $foreachNode = $this->createForeachNode(new Variable('settings'), 'item', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertCount(1, $errorList);
        self::assertStringContainsString('inferred value type: "string"', $errorList[0]->getMessage());
    }

    /**
     * @throws Throwable
     */
    public function testForeachScalarIterableValueMismatchUsesScalarValueTypeDescriptionFails(): void
    {
        $scope       = $this->createScopeStubWithType($this->createStringArrayType());
        $foreachNode = $this->createForeachNode(new Variable('settings'), 'item', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertCount(1, $errorList);
        self::assertStringContainsString('inferred value type: "string"', $errorList[0]->getMessage());
    }

    /**
     * @throws Throwable
     */
    public function testForeachObjectIterableWithoutNamespaceUsesShortClassNameInMessageFails(): void
    {
        $scope = $this->createScopeStubWithType(
            new ArrayType(
                keyType: new IntegerType(),
                itemType: new ObjectType('ChildNode'),
            ),
        );
        $foreachNode = $this->createForeachNode(new Variable('settings'), 'item', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertCount(1, $errorList);
        self::assertStringContainsString('inferred value type: "ChildNode"', $errorList[0]->getMessage());
    }

    /**
     * @throws Throwable
     */
    public function testRecursiveIteratorTypeSkipsNamingValidationSucceeds(): void
    {
        $scope       = $this->createScopeStubWithType($this->createRecursiveIteratorType());
        $foreachNode = $this->createForeachNode(new Variable('fileIterator'), 'fileInfo', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testRecursiveIteratorIteratorTypeSkipsNamingValidationSucceeds(): void
    {
        $scope       = $this->createScopeStubWithType($this->createRecursiveIteratorIteratorType());
        $foreachNode = $this->createForeachNode(new Variable('fileIterator'), 'fileInfo', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    #[RunInSeparateProcess]
    public function testFallbackTypeCandidateNormalizationWithoutReflectionProviderFails(): void
    {
        $iterableType = new ArrayType(
            keyType: new IntegerType(),
            itemType: new UnionType([
                new ObjectType('AbstractChannelInterface'),
                new ObjectType('Node123'),
                new ObjectType('FooBar'),
                new ObjectType('___'),
            ]),
        );
        $scope       = $this->createScopeStubWithType($iterableType);
        $foreachNode = $this->createForeachNode(new Variable('settings'), 'item', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertCount(1, $errorList);
    }

    /**
     * @throws Throwable
     */
    #[RunInSeparateProcess]
    public function testRecursiveIteratorMissingAccessorFallbackWithMatchSucceeds(): void
    {
        $iterableType = $this->createIterableTypeStub(
            iterableValueType: new StringType(),
            isIterable: false,
            objectClassNameList: [\RecursiveIterator::class],
        );
        $scope       = $this->createScopeStubWithType($iterableType);
        $foreachNode = $this->createForeachNode(new Variable('fileIterator'), 'fileInfo', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    #[RunInSeparateProcess]
    public function testRecursiveIteratorMissingAccessorFallbackWithoutMatchSucceeds(): void
    {
        $iterableType = $this->createIterableTypeStub(
            iterableValueType: new StringType(),
            isIterable: false,
            objectClassNameList: ['UnknownIterableType'],
        );
        $scope = $this->createScopeStubWithType($iterableType);

        $expressionNode = new MethodCall(
            var: new Variable('this'),
            name: new Identifier('resolveUnknownIterable'),
            attributes: ['startLine' => 14],
        );
        $foreachNode = $this->createForeachNode($expressionNode, 'item', 14);
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

    /**
     * @throws Throwable
     */
    public function testForeachValueNameIntersectionTypeMismatchFails(): void
    {
        $scope       = $this->createScopeStubWithType($this->createIntersectionChildNodeArrayType());
        $foreachNode = $this->createForeachNode(new Variable('children'), 'item', 14);
        $errorList   = $this->rule->processNode($foreachNode, $scope);

        self::assertCount(1, $errorList);

        $ruleError = $errorList[0];
        self::assertSame(self::FOREACH_INTERSECTION_ERROR_MESSAGE, $ruleError->getMessage());

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
    public function testScopeTypeIsResolvedOnceForMismatchMessageFails(): void
    {
        /** @var MockObject&NodeCallbackInvoker&Scope $scope */
        $scope = self::createMockForIntersectionOfInterfaces([Scope::class, NodeCallbackInvoker::class]);
        $scope->expects(self::once())
            ->method('getType')
            ->willReturn($this->createChildNodeArrayType());

        $foreachNode = $this->createForeachNode(new Variable('children'), 'item', 14);
        $this->rule->processNode($foreachNode, $scope);
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

    private function createIntersectionChildNodeArrayType(): Type
    {
        return new ArrayType(
            keyType: new IntegerType(),
            itemType: new IntersectionType([
                new ObjectType('LoopValueVariableNamingFixtures\Invalid\ChildNode'),
                new ObjectType('LoopValueVariableNamingFixtures\Invalid\NodeInterface'),
            ]),
        );
    }

    private function createStringArrayType(): Type
    {
        return new ArrayType(
            keyType: new IntegerType(),
            itemType: new StringType(),
        );
    }

    private function createRecursiveIteratorType(): Type
    {
        /** @var Stub&Type $type */
        $type = self::createStub(Type::class);
        $type->method('getObjectClassNames')
            ->willReturn([\RecursiveIterator::class]);

        return $type;
    }

    private function createRecursiveIteratorIteratorType(): Type
    {
        /** @var Stub&Type $type */
        $type = self::createStub(Type::class);
        $type->method('getObjectClassNames')
            ->willReturn([\RecursiveIteratorIterator::class]);

        return $type;
    }

    /**
     * @param array<int, string> $objectClassNameList
     */
    private function createIterableTypeStub(
        Type $iterableValueType,
        bool $isIterable = true,
        array $objectClassNameList = [],
    ): Type {
        /** @var Stub&Type $iterableType */
        $iterableType = self::createStub(Type::class);
        $iterableType->method('isIterable')
            ->willReturn($isIterable === true ? TrinaryLogic::createYes() : TrinaryLogic::createNo());
        $iterableType->method('getIterableValueType')
            ->willReturn($iterableValueType);
        $iterableType->method('getObjectClassNames')
            ->willReturn($objectClassNameList);

        return $iterableType;
    }

    private function createForeachNodeWithValueExpression(
        Node\Expr $iterableExpressionNode,
        Node\Expr $valueExpressionNode,
        int $line,
        ?string $keyVariableName = null,
    ): Foreach_ {
        $subNodeList = ['stmts' => []];

        if ($keyVariableName !== null) {
            $subNodeList['keyVar'] = new Variable(
                name: $keyVariableName,
                attributes: ['startLine' => $line],
            );
        }

        return new Foreach_(
            expr: $iterableExpressionNode,
            valueVar: $valueExpressionNode,
            subNodes: $subNodeList,
            attributes: ['startLine' => $line],
        );
    }

    private function createForeachNode(
        Node\Expr $iterableExpressionNode,
        string $valueVariableName,
        int $line,
        ?string $keyVariableName = null,
    ): Foreach_ {
        $valueVariableNode = new Variable(
            name: $valueVariableName,
            attributes: ['startLine' => $line],
        );

        $subNodeList = ['stmts' => []];

        if ($keyVariableName !== null) {
            $subNodeList['keyVar'] = new Variable(
                name: $keyVariableName,
                attributes: ['startLine' => $line],
            );
        }

        return new Foreach_(
            expr: $iterableExpressionNode,
            valueVar: $valueVariableNode,
            subNodes: $subNodeList,
            attributes: ['startLine' => $line],
        );
    }
}
