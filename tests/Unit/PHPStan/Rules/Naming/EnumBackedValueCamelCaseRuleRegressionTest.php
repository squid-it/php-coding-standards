<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\NodeCallbackInvoker;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\LineRuleError;
use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\EnumBackedValueCamelCaseRule;
use Throwable;

final class EnumBackedValueCamelCaseRuleRegressionTest extends TestCase
{
    private const string TOTAL_METHOD_ERROR         = 'Backed enum value "foo_bar" on case "TotalMethodDoesNotWhitelist::FooBar" must be camelCase unless the same literal is referenced by a to*() method.';
    private const string NON_RETURN_TO_METHOD_ERROR = 'Backed enum value "foo_bar" on case "ToMethodExceptionLiteralDoesNotWhitelist::FooBar" must be camelCase unless the same literal is referenced by a to*() method.';

    private EnumBackedValueCamelCaseRule $rule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule = new EnumBackedValueCamelCaseRule();
    }

    /**
     * @throws Throwable
     */
    public function testTotalMethodPrefixDoesNotWhitelistFails(): void
    {
        $enumNode = $this->createStringBackedEnumNode(
            enumName: 'TotalMethodDoesNotWhitelist',
            caseValue: 'foo_bar',
            caseLine: 9,
            methodList: [
                $this->createClassMethod(
                    methodName: 'total',
                    statementList: [
                        new Return_(
                            expr: new String_('foo_bar', ['startLine' => 14]),
                            attributes: ['startLine' => 14],
                        ),
                    ],
                    line: 12,
                ),
            ],
        );
        $errorList = $this->rule->processNode($enumNode, $this->createScopeStub());

        self::assertCount(1, $errorList);

        $ruleError = $errorList[0];
        self::assertSame(self::TOTAL_METHOD_ERROR, $ruleError->getMessage());

        if (($ruleError instanceof IdentifierRuleError) === false) {
            self::fail('Expected IdentifierRuleError implementation.');
        }

        self::assertSame('squidit.naming.enumBackedValueCamelCase', $ruleError->getIdentifier());

        if (($ruleError instanceof LineRuleError) === false) {
            self::fail('Expected LineRuleError implementation.');
        }

        self::assertSame(9, $ruleError->getLine());
    }

    /**
     * @throws Throwable
     */
    public function testToMethodNonReturnLiteralDoesNotWhitelistFails(): void
    {
        $throwExpression = new Throw_(
            expr: new New_(
                class: new Name('\InvalidArgumentException'),
                args: [
                    new Arg(
                        value: new String_('foo_bar', ['startLine' => 17]),
                        attributes: ['startLine' => 17],
                    ),
                ],
                attributes: ['startLine' => 17],
            ),
            attributes: ['startLine' => 17],
        );
        $enumNode = $this->createStringBackedEnumNode(
            enumName: 'ToMethodExceptionLiteralDoesNotWhitelist',
            caseValue: 'foo_bar',
            caseLine: 9,
            methodList: [
                $this->createClassMethod(
                    methodName: 'toDb',
                    statementList: [
                        new If_(
                            cond: new Identical(
                                left: new Variable('strict'),
                                right: new ConstFetch(new Name('true')),
                            ),
                            subNodes: [
                                'stmts' => [
                                    new Expression(
                                        expr: $throwExpression,
                                        attributes: ['startLine' => 17],
                                    ),
                                ],
                                'elseifs' => [],
                                'else'    => null,
                            ],
                            attributes: ['startLine' => 16],
                        ),
                        new Return_(
                            expr: new String_('fooBar', ['startLine' => 20]),
                            attributes: ['startLine' => 20],
                        ),
                    ],
                    line: 12,
                ),
            ],
        );
        $errorList = $this->rule->processNode($enumNode, $this->createScopeStub());

        self::assertCount(1, $errorList);
        self::assertSame(self::NON_RETURN_TO_METHOD_ERROR, $errorList[0]->getMessage());
    }

    /**
     * @param array<int, ClassMethod> $methodList
     */
    private function createStringBackedEnumNode(string $enumName, string $caseValue, int $caseLine, array $methodList): Enum_
    {
        $statementList = [
            new EnumCase(
                name: new Identifier('FooBar'),
                expr: new String_($caseValue, ['startLine' => $caseLine]),
                attributes: ['startLine' => $caseLine],
            ),
        ];

        foreach ($methodList as $methodNode) {
            $statementList[] = $methodNode;
        }

        return new Enum_(
            name: new Identifier($enumName),
            subNodes: [
                'scalarType' => new Identifier('string'),
                'implements' => [],
                'stmts'      => $statementList,
            ],
            attributes: ['startLine' => 1],
        );
    }

    /**
     * @param array<int, If_|Return_> $statementList
     */
    private function createClassMethod(string $methodName, array $statementList, int $line): ClassMethod
    {
        return new ClassMethod(
            name: $methodName,
            subNodes: [
                'stmts'      => $statementList,
                'returnType' => new Identifier('string'),
            ],
            attributes: ['startLine' => $line],
        );
    }

    private function createScopeStub(): Scope&NodeCallbackInvoker
    {
        $scope = self::createStubForIntersectionOfInterfaces([Scope::class, NodeCallbackInvoker::class]);

        if (($scope instanceof Scope) === false || ($scope instanceof NodeCallbackInvoker) === false) {
            self::fail('Unable to create scope stub.');
        }

        return $scope;
    }
}
