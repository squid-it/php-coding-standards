<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture;

use PhpParser\Node\Stmt\Class_;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Architecture\ReadonlyClassPromotionRule;
use Throwable;

/**
 * @extends RuleTestCase<ReadonlyClassPromotionRule>
 */
final class ReadonlyClassPromotionRuleTest extends RuleTestCase
{
    private const string FIXTURES_DIR                 = __DIR__ . '/Fixtures/ReadonlyClassPromotion';
    private const string FINAL_CLASS_ERROR_MESSAGE    = 'Final class "FinalClassWithReadonlyProperties" can be declared readonly because all declared properties are readonly. Convert it to a readonly class and remove property-level readonly modifiers.';
    private const string PROMOTED_CLASS_ERROR_MESSAGE = 'Final class "FinalClassWithReadonlyPromotedProperties" can be declared readonly because all declared properties are readonly. Convert it to a readonly class and remove property-level readonly modifiers.';
    private const string MIXED_CLASS_ERROR_MESSAGE    = 'Final class "FinalClassWithReadonlyAndPromotedReadonlyProperties" can be declared readonly because all declared properties are readonly. Convert it to a readonly class and remove property-level readonly modifiers.';

    protected function getRule(): Rule
    {
        return new ReadonlyClassPromotionRule();
    }

    /**
     * @throws Throwable
     */
    public function testGetNodeTypeReturnsClassNodeTypeSucceeds(): void
    {
        self::assertSame(Class_::class, new ReadonlyClassPromotionRule()->getNodeType());
    }

    /**
     * @throws Throwable
     */
    public function testAlreadyReadonlyClassSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/AlreadyReadonlyClass.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testFinalClassWithMutablePropertySucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/FinalClassWithMutableProperty.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testFinalClassWithoutPropertiesSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/FinalClassWithoutProperties.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testNonFinalClassWithReadonlyPropertiesSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/NonFinalClassWithReadonlyProperties.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testFinalClassWithPromotedMutablePropertySucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/FinalClassWithPromotedMutableProperty.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testFinalClassExtendingParentWithReadonlyPropertiesSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/FinalClassExtendingParentWithReadonlyProperties.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testFinalClassWithReadonlyPropertiesFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/FinalClassWithReadonlyProperties.php'], [
            [self::FINAL_CLASS_ERROR_MESSAGE, 7],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testFinalClassWithReadonlyPromotedPropertiesFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/FinalClassWithReadonlyPromotedProperties.php'], [
            [self::PROMOTED_CLASS_ERROR_MESSAGE, 7],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testFinalClassWithReadonlyAndPromotedReadonlyPropertiesFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/FinalClassWithReadonlyAndPromotedReadonlyProperties.php'], [
            [self::MIXED_CLASS_ERROR_MESSAGE, 7],
        ]);
    }
}
