<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SquidIT\PhpCodingStandards\PHPStan\Rules\DisallowLogicalNotRule;
use Throwable;

/**
 * @extends RuleTestCase<DisallowLogicalNotRule>
 */
final class DisallowLogicalNotRuleTest extends RuleTestCase
{
    private const string FIXTURES_DIR  = __DIR__ . '/Fixtures/DisallowLogicalNot';
    private const string ERROR_MESSAGE = 'Using logical NOT (!) is not allowed. Use an explicit comparison instead (=== true, === false, !== null).';

    protected function getRule(): Rule
    {
        return new DisallowLogicalNotRule();
    }

    /**
     * @throws Throwable
     */
    public function testExplicitTrueComparisonSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/ExplicitTrueComparison.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testExplicitFalseComparisonSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/ExplicitFalseComparison.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testExplicitNullComparisonSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/ExplicitNullComparison.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testExplicitComparisonInConditionSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/ExplicitComparisonInCondition.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testFunctionReturnComparisonSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/FunctionReturnComparison.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testLogicalNotVariableFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/LogicalNotVariable.php'], [
            [self::ERROR_MESSAGE, 8],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testLogicalNotFunctionCallFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/LogicalNotFunctionCall.php'], [
            [self::ERROR_MESSAGE, 8],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testLogicalNotInConditionFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/LogicalNotInCondition.php'], [
            [self::ERROR_MESSAGE, 9],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testLogicalNotMethodCallFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/LogicalNotMethodCall.php'], [
            [self::ERROR_MESSAGE, 16],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testLogicalNotDoubleNegationFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/LogicalNotDoubleNegation.php'], [
            [self::ERROR_MESSAGE, 8],
            [self::ERROR_MESSAGE, 8],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testMultipleViolationsFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/MultipleViolations.php'], [
            [self::ERROR_MESSAGE, 10],
            [self::ERROR_MESSAGE, 11],
            [self::ERROR_MESSAGE, 13],
        ]);
    }
}
