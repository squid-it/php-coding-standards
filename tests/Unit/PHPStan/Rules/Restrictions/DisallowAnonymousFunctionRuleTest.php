<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Restrictions;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Restrictions\DisallowAnonymousFunctionRule;
use Throwable;

/**
 * @extends RuleTestCase<DisallowAnonymousFunctionRule>
 */
final class DisallowAnonymousFunctionRuleTest extends RuleTestCase
{
    private const string FIXTURES_DIR = __DIR__ . '/Fixtures/DisallowAnonymousFunction';

    protected function getRule(): Rule
    {
        return new DisallowAnonymousFunctionRule();
    }

    /**
     * @throws Throwable
     */
    public function testClassWithInvokeMethodSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/ClassWithInvokeMethod.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testRegularClassWithMethodsSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/RegularClassWithMethods.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testRegularFunctionSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/RegularFunction.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testFirstClassCallableSyntaxSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/FirstClassCallableSyntax.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testAnonymousFunctionFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/AnonymousFunction.php'], [
            [
                'Anonymous functions (closures) are not allowed. Use an invokable class with an __invoke() method instead.',
                7,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testArrowFunctionFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/ArrowFunction.php'], [
            [
                'Arrow functions are not allowed. Use an invokable class with an __invoke() method instead.',
                7,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testStaticAnonymousFunctionFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/StaticAnonymousFunction.php'], [
            [
                'Anonymous functions (closures) are not allowed. Use an invokable class with an __invoke() method instead.',
                7,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testAnonymousFunctionAsArgumentFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/AnonymousFunctionAsArgument.php'], [
            [
                'Anonymous functions (closures) are not allowed. Use an invokable class with an __invoke() method instead.',
                9,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testArrowFunctionAsArgumentFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/ArrowFunctionAsArgument.php'], [
            [
                'Arrow functions are not allowed. Use an invokable class with an __invoke() method instead.',
                9,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testAnonymousFunctionWithUseFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/AnonymousFunctionWithUse.php'], [
            [
                'Anonymous functions (closures) are not allowed. Use an invokable class with an __invoke() method instead.',
                9,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testMultipleViolationsFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/MultipleViolations.php'], [
            [
                'Anonymous functions (closures) are not allowed. Use an invokable class with an __invoke() method instead.',
                7,
            ],
            [
                'Arrow functions are not allowed. Use an invokable class with an __invoke() method instead.',
                11,
            ],
            [
                'Anonymous functions (closures) are not allowed. Use an invokable class with an __invoke() method instead.',
                15,
            ],
        ]);
    }
}
