<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\Analyser\NodeCallbackInvoker;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\LineRuleError;
use PHPStan\Type\ObjectType;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\LoggerContextKeyCamelCaseRule;
use Throwable;

final class LoggerContextKeyCamelCaseRuleTest extends TestCase
{
    private const string FOO_BAR_ERROR     = 'Logger context key "foo_bar" in info() must be camelCase.';
    private const string USER_ID_LOG_ERROR = 'Logger context key "user_id" in log() must be camelCase.';

    private LoggerContextKeyCamelCaseRule $rule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule = new LoggerContextKeyCamelCaseRule();
    }

    /**
     * @throws Throwable
     */
    public function testLoggerContextCamelCaseKeySucceeds(): void
    {
        $contextArrayNode = new Array_([
            new ArrayItem(
                value: new LNumber(1),
                key: new String_('fooBar', ['startLine' => 20]),
                attributes: ['startLine' => 20],
            ),
        ], ['startLine' => 20]);
        $methodCallNode = $this->createMethodCallNode(
            methodName: 'info',
            argumentList: [
                new String_('saved'),
                $contextArrayNode,
            ],
            line: 20,
        );
        $errorList = $this->rule->processNode(
            $methodCallNode,
            $this->createScopeStubWithReceiverType(new ObjectType(LoggerInterface::class)),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testLoggerContextSnakeCaseKeyFails(): void
    {
        $contextArrayNode = new Array_([
            new ArrayItem(
                value: new LNumber(1),
                key: new String_('foo_bar', ['startLine' => 24]),
                attributes: ['startLine' => 24],
            ),
        ], ['startLine' => 24]);
        $methodCallNode = $this->createMethodCallNode(
            methodName: 'info',
            argumentList: [
                new String_('saved'),
                $contextArrayNode,
            ],
            line: 24,
        );
        $errorList = $this->rule->processNode(
            $methodCallNode,
            $this->createScopeStubWithReceiverType(new ObjectType(LoggerInterface::class)),
        );

        self::assertCount(1, $errorList);

        $ruleError = $errorList[0];
        self::assertSame(self::FOO_BAR_ERROR, $ruleError->getMessage());

        if (($ruleError instanceof IdentifierRuleError) === false) {
            self::fail('Expected IdentifierRuleError implementation.');
        }

        self::assertSame('squidit.naming.loggerContextKeyCamelCase', $ruleError->getIdentifier());

        if (($ruleError instanceof LineRuleError) === false) {
            self::fail('Expected LineRuleError implementation.');
        }

        self::assertSame(24, $ruleError->getLine());
    }

    /**
     * @throws Throwable
     */
    public function testLoggerLogMethodUsesThirdContextArgumentFails(): void
    {
        $contextArrayNode = new Array_([
            new ArrayItem(
                value: new LNumber(1),
                key: new String_('user_id', ['startLine' => 30]),
                attributes: ['startLine' => 30],
            ),
        ], ['startLine' => 30]);
        $methodCallNode = $this->createMethodCallNode(
            methodName: 'log',
            argumentList: [
                new String_('info'),
                new String_('saved'),
                $contextArrayNode,
            ],
            line: 30,
        );
        $errorList = $this->rule->processNode(
            $methodCallNode,
            $this->createScopeStubWithReceiverType(new ObjectType(LoggerInterface::class)),
        );

        self::assertCount(1, $errorList);
        self::assertSame(self::USER_ID_LOG_ERROR, $errorList[0]->getMessage());
    }

    /**
     * @throws Throwable
     */
    public function testDynamicKeyAndNonLoggerReceiverAreIgnoredSucceeds(): void
    {
        $contextArrayNode = new Array_([
            new ArrayItem(
                value: new LNumber(1),
                key: new Variable('dynamicKey', ['startLine' => 35]),
                attributes: ['startLine' => 35],
            ),
        ], ['startLine' => 35]);
        $dynamicKeyMethodCallNode = $this->createMethodCallNode(
            methodName: 'warning',
            argumentList: [
                new String_('saved'),
                $contextArrayNode,
            ],
            line: 35,
        );
        $dynamicKeyErrorList = $this->rule->processNode(
            $dynamicKeyMethodCallNode,
            $this->createScopeStubWithReceiverType(new ObjectType(LoggerInterface::class)),
        );

        self::assertSame([], $dynamicKeyErrorList);

        $snakeCaseContextArrayNode = new Array_([
            new ArrayItem(
                value: new LNumber(1),
                key: new String_('foo_bar', ['startLine' => 36]),
                attributes: ['startLine' => 36],
            ),
        ], ['startLine' => 36]);
        $nonLoggerMethodCallNode = $this->createMethodCallNode(
            methodName: 'info',
            argumentList: [
                new String_('saved'),
                $snakeCaseContextArrayNode,
            ],
            line: 36,
        );
        $nonLoggerErrorList = $this->rule->processNode(
            $nonLoggerMethodCallNode,
            $this->createScopeStubWithReceiverType(new ObjectType(\stdClass::class)),
        );

        self::assertSame([], $nonLoggerErrorList);
    }

    /**
     * @throws Throwable
     */
    public function testUnsupportedLoggerMethodIsIgnoredSucceeds(): void
    {
        $methodCallNode = $this->createMethodCallNode(
            methodName: 'customLogMethod',
            argumentList: [
                new String_('saved'),
                new Array_([], ['startLine' => 40]),
            ],
            line: 40,
        );
        $errorList = $this->rule->processNode(
            $methodCallNode,
            $this->createScopeStubWithReceiverType(new ObjectType(LoggerInterface::class)),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testMissingContextArgumentIsIgnoredSucceeds(): void
    {
        $methodCallNode = $this->createMethodCallNode(
            methodName: 'info',
            argumentList: [new String_('saved')],
            line: 44,
        );
        $errorList = $this->rule->processNode(
            $methodCallNode,
            $this->createScopeStubWithReceiverType(new ObjectType(LoggerInterface::class)),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testNonArrayContextArgumentIsIgnoredSucceeds(): void
    {
        $methodCallNode = $this->createMethodCallNode(
            methodName: 'info',
            argumentList: [
                new String_('saved'),
                new String_('context'),
            ],
            line: 48,
        );
        $errorList = $this->rule->processNode(
            $methodCallNode,
            $this->createScopeStubWithReceiverType(new ObjectType(LoggerInterface::class)),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testDynamicMethodNameIsIgnoredSucceeds(): void
    {
        $dynamicMethodCallNode = new MethodCall(
            var: new Variable('logger', ['startLine' => 52]),
            name: new Variable('methodName', ['startLine' => 52]),
            args: [],
            attributes: ['startLine' => 52],
        );
        $dynamicNameErrorList = $this->rule->processNode(
            $dynamicMethodCallNode,
            $this->createScopeStubWithReceiverType(new ObjectType(LoggerInterface::class)),
        );

        self::assertSame([], $dynamicNameErrorList);
    }

    /**
     * @throws Throwable
     */
    public function testNonArgContextSlotAndMixedArrayItemsSucceeds(): void
    {
        $loggerMethodCallWithPlaceholderContextNode = new MethodCall(
            var: new Variable('logger', ['startLine' => 56]),
            name: new Identifier('info'),
            args: [
                new Arg(
                    value: new String_('saved'),
                    attributes: ['startLine' => 56],
                ),
                new VariadicPlaceholder(['startLine' => 56]),
            ],
            attributes: ['startLine' => 56],
        );
        $placeholderErrorList = $this->rule->processNode(
            $loggerMethodCallWithPlaceholderContextNode,
            $this->createScopeStubWithReceiverType(new ObjectType(LoggerInterface::class)),
        );

        self::assertSame([], $placeholderErrorList);

        $mixedContextArrayNode = new Array_([
            new ArrayItem(
                value: new LNumber(1),
                key: new LNumber(1),
                attributes: ['startLine' => 58],
            ),
            new ArrayItem(
                value: new LNumber(1),
                key: new String_('fooBar', ['startLine' => 58]),
                attributes: ['startLine' => 58],
            ),
        ], ['startLine' => 58]);
        $mixedMethodCallNode = $this->createMethodCallNode(
            methodName: 'info',
            argumentList: [
                new String_('saved'),
                $mixedContextArrayNode,
            ],
            line: 58,
        );
        $mixedArrayErrorList = $this->rule->processNode(
            $mixedMethodCallNode,
            $this->createScopeStubWithReceiverType(new ObjectType(LoggerInterface::class)),
        );

        self::assertSame([], $mixedArrayErrorList);
    }

    /**
     * @param array<int, \PhpParser\Node\Expr> $argumentList
     */
    private function createMethodCallNode(string $methodName, array $argumentList, int $line): MethodCall
    {
        $methodCallArgumentList = [];

        foreach ($argumentList as $argumentValueNode) {
            $methodCallArgumentList[] = new Arg(
                value: $argumentValueNode,
                attributes: ['startLine' => $line],
            );
        }

        return new MethodCall(
            var: new Variable('logger', ['startLine' => $line]),
            name: new Identifier($methodName),
            args: $methodCallArgumentList,
            attributes: ['startLine' => $line],
        );
    }

    private function createScopeStubWithReceiverType(ObjectType $receiverType): Scope&NodeCallbackInvoker
    {
        /** @var NodeCallbackInvoker&Scope&Stub $scope */
        $scope = self::createStubForIntersectionOfInterfaces([Scope::class, NodeCallbackInvoker::class]);
        $scope->method('getType')->willReturn($receiverType);

        return $scope;
    }
}
