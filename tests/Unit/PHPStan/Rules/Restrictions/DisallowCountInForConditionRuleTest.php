<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Restrictions;

use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\Analyser\CollectedDataEmitter;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\NodeCallbackInvoker;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\LineRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Restrictions\DisallowCountInForConditionRule;
use Throwable;

/**
 * @extends RuleTestCase<DisallowCountInForConditionRule>
 */
final class DisallowCountInForConditionRuleTest extends RuleTestCase
{
    private const string FIXTURES_DIR  = __DIR__ . '/Fixtures/DisallowCountInForCondition';
    private const string ERROR_MESSAGE = 'Calling count() in the second expression of a for loop is not allowed. Calculate the count in the first expression (the initializer) instead.';

    protected function getRule(): Rule
    {
        return new DisallowCountInForConditionRule(self::createReflectionProvider());
    }

    /**
     * @return array<int, string>
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/DisallowCountInForConditionRuleTest.neon'];
    }

    public function testGetNodeTypeSucceeds(): void
    {
        self::assertSame(For_::class, $this->getRule()->getNodeType());
    }

    /**
     * @throws Throwable
     */
    public function testCountCallsInConditionsFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/CountCalls.php'], [
            [self::ERROR_MESSAGE, 14],
            [self::ERROR_MESSAGE, 18],
            [self::ERROR_MESSAGE, 22],
            [self::ERROR_MESSAGE, 26],
            [self::ERROR_MESSAGE, 30],
            [self::ERROR_MESSAGE, 34],
            [self::ERROR_MESSAGE, 38],
            [self::ERROR_MESSAGE, 38],
            [self::ERROR_MESSAGE, 44],
            [self::ERROR_MESSAGE, 51],
            [self::ERROR_MESSAGE, 56],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testAliasedCountCallFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/AliasedCount.php'], [
            [self::ERROR_MESSAGE, 14],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testCountInFirstAndThirdExpressionsAndLoopBodySucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/CountOutsideConditions.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testNamespacedCountFunctionSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/NamespacedCount.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testCountMethodsSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/CountMethods.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testDeferredBodiesAndFirstClassCallablesSucceeds(): void
    {
        $reflectionProvider              = $this->createStub(ReflectionProvider::class);
        $scope                           = $this->createRuleScopeStub();
        $disallowCountInForConditionRule = new DisallowCountInForConditionRule($reflectionProvider);
        $funcCall                        = new FuncCall(new Name('count'));
        $for                             = new For_(['cond' => [
            new ArrowFunction(['expr' => $funcCall]),
            new Closure(['stmts' => [new Expression($funcCall)]]),
            new FuncCall(new Name('count'), [new VariadicPlaceholder()]),
            new New_(new Class_(null, ['stmts' => [
                new ClassMethod('count', ['stmts' => [new Expression($funcCall)]]),
            ]])),
        ]]);

        self::assertSame([], $disallowCountInForConditionRule->processNode($for, $scope));
    }

    /**
     * @throws Throwable
     */
    public function testDynamicFunctionCallSucceeds(): void
    {
        $reflectionProvider              = $this->createStub(ReflectionProvider::class);
        $scope                           = $this->createRuleScopeStub();
        $disallowCountInForConditionRule = new DisallowCountInForConditionRule($reflectionProvider);
        $for                             = new For_(['cond' => [new FuncCall(new Variable('callback'))]]);

        self::assertSame([], $disallowCountInForConditionRule->processNode($for, $scope));
    }

    /**
     * @throws Throwable
     */
    public function testErrorIdentifierAndCallLineSucceeds(): void
    {
        $reflectionProvider = $this->createStub(ReflectionProvider::class);
        $reflectionProvider
            ->method('resolveFunctionName')
            ->willReturn('count');
        $scope                           = $this->createRuleScopeStub();
        $disallowCountInForConditionRule = new DisallowCountInForConditionRule($reflectionProvider);
        $for                             = new For_(['cond' => [new FuncCall(new Name('count'), [], ['startLine' => 12])]]);

        $ruleErrorList = $disallowCountInForConditionRule->processNode($for, $scope);

        self::assertCount(1, $ruleErrorList);
        self::assertSame(self::ERROR_MESSAGE, $ruleErrorList[0]->getMessage());
        self::assertInstanceOf(IdentifierRuleError::class, $ruleErrorList[0]);
        self::assertSame('squidit.restrictions.disallowCountInForCondition', $ruleErrorList[0]->getIdentifier());
        self::assertInstanceOf(LineRuleError::class, $ruleErrorList[0]);
        self::assertSame(12, $ruleErrorList[0]->getLine());
    }

    /**
     * @throws Throwable
     */
    private function createRuleScopeStub(): Scope&NodeCallbackInvoker&CollectedDataEmitter&DependencyTracker
    {
        $stub = $this->createStubForIntersectionOfInterfaces([
            Scope::class,
            NodeCallbackInvoker::class,
            CollectedDataEmitter::class,
            DependencyTracker::class,
        ]);

        self::assertInstanceOf(Scope::class, $stub);
        self::assertInstanceOf(NodeCallbackInvoker::class, $stub);
        self::assertInstanceOf(CollectedDataEmitter::class, $stub);
        self::assertInstanceOf(DependencyTracker::class, $stub);

        return $stub;
    }
}
