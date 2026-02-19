<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming;

use PhpParser\Node\Stmt\Enum_;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\EnumBackedValueCamelCaseRule;
use Throwable;

/**
 * @extends RuleTestCase<EnumBackedValueCamelCaseRule>
 */
final class EnumBackedValueCamelCaseRuleTest extends RuleTestCase
{
    private const string FIXTURES_DIR                       = __DIR__ . '/Fixtures/EnumBackedValueCamelCase';
    private const string ERROR_MESSAGE                      = 'Backed enum value "foo_bar" on case "SnakeCaseBackedValue::FooBar" must be camelCase unless the same literal is referenced by a to*() method.';
    private const string NON_TO_ERROR_MESSAGE               = 'Backed enum value "foo_bar" on case "SnakeCaseOnlyReferencedByNonToMethod::FooBar" must be camelCase unless the same literal is referenced by a to*() method.';
    private const string DIFFERENT_TO_LITERAL_ERROR_MESSAGE = 'Backed enum value "foo_bar" on case "SnakeCaseWithDifferentToLiteral::FooBar" must be camelCase unless the same literal is referenced by a to*() method.';

    protected function getRule(): Rule
    {
        return new EnumBackedValueCamelCaseRule();
    }

    /**
     * @throws Throwable
     */
    public function testGetNodeTypeReturnsEnumClassSucceeds(): void
    {
        self::assertSame(Enum_::class, (new EnumBackedValueCamelCaseRule())->getNodeType());
    }

    /**
     * @throws Throwable
     */
    public function testCamelCaseBackedValueSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/CamelCaseBackedValue.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testSnakeCaseBackedValueFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/SnakeCaseBackedValue.php'], [
            [self::ERROR_MESSAGE, 9],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testSnakeCaseBackedValueReferencedByToMethodSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/SnakeCaseReferencedByToMethod.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testSnakeCaseBackedValueReferencedByNonToMethodFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/SnakeCaseOnlyReferencedByNonToMethod.php'], [
            [self::NON_TO_ERROR_MESSAGE, 9],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testSnakeCaseBackedValueWithDifferentToLiteralFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/SnakeCaseWithDifferentToLiteral.php'], [
            [self::DIFFERENT_TO_LITERAL_ERROR_MESSAGE, 9],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testUnitAndIntBackedEnumsAreIgnoredSucceeds(): void
    {
        $this->analyse([
            self::FIXTURES_DIR . '/Valid/IntBackedEnumIgnored.php',
            self::FIXTURES_DIR . '/Valid/UnitEnumIgnored.php',
        ], []);
    }

    /**
     * @throws Throwable
     */
    public function testNonLiteralBackedValueExpressionIsIgnoredSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/NonLiteralBackedValueIgnored.php'], []);
    }
}
