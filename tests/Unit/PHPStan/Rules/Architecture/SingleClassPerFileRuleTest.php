<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Architecture\SingleClassPerFileRule;
use Throwable;

/**
 * @extends RuleTestCase<SingleClassPerFileRule>
 */
final class SingleClassPerFileRuleTest extends RuleTestCase
{
    private const string FIXTURES_DIR = __DIR__ . '/Fixtures/SingleClassPerFile';

    protected function getRule(): Rule
    {
        return new SingleClassPerFileRule();
    }

    /**
     * @throws Throwable
     */
    public function testSingleClassSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/SingleClass.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testSingleInterfaceSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/SingleInterface.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testSingleTraitSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/SingleTrait.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testSingleEnumSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/SingleEnum.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testClassWithAnonymousClassSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/ClassWithAnonymousClass.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testSingleClassNoNamespaceSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/SingleClassNoNamespace.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testEmptyFileSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/EmptyFile.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testTwoClassesFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/TwoClasses.php'], [
            [
                'File must contain a single class-like declaration. Found Class "TwoClassesFirst" and Class "TwoClassesSecond"',
                9,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testClassAndInterfaceFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/ClassAndInterface.php'], [
            [
                'File must contain a single class-like declaration. Found Class "ClassAndInterfaceClass" and Interface "ClassAndInterfaceInterface"',
                9,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testClassAndTraitFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/ClassAndTrait.php'], [
            [
                'File must contain a single class-like declaration. Found Class "ClassAndTraitClass" and Trait "ClassAndTraitTrait"',
                9,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testClassAndEnumFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/ClassAndEnum.php'], [
            [
                'File must contain a single class-like declaration. Found Class "ClassAndEnumClass" and Enum "ClassAndEnumEnum"',
                9,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testTwoInterfacesFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/TwoInterfaces.php'], [
            [
                'File must contain a single class-like declaration. Found Interface "TwoInterfacesFirst" and Interface "TwoInterfacesSecond"',
                9,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testThreeClassesFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/ThreeClasses.php'], [
            [
                'File must contain a single class-like declaration. Found Class "ThreeClassesFirst" and Class "ThreeClassesSecond"',
                9,
            ],
            [
                'File must contain a single class-like declaration. Found Class "ThreeClassesFirst" and Class "ThreeClassesThird"',
                11,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testTwoClassesInNamespaceFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/TwoClassesInNamespace.php'], [
            [
                'File must contain a single class-like declaration. Found Class "TwoClassesInNamespaceFirst" and Class "TwoClassesInNamespaceSecond"',
                9,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testTwoClassesNoNamespaceFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/TwoClassesNoNamespace.php'], [
            [
                'File must contain a single class-like declaration. Found Class "TwoClassesNoNamespaceFirst" and Class "TwoClassesNoNamespaceSecond"',
                7,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testTwoEnumsFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/TwoEnums.php'], [
            [
                'File must contain a single class-like declaration. Found Enum "TwoEnumsFirst" and Enum "TwoEnumsSecond"',
                12,
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testTwoTraitsFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/TwoTraits.php'], [
            [
                'File must contain a single class-like declaration. Found Trait "TwoTraitsFirst" and Trait "TwoTraitsSecond"',
                9,
            ],
        ]);
    }
}
