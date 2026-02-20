<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support;

use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PHPStan\Support\NameNormalizer;
use Throwable;

final class NameNormalizerTest extends TestCase
{
    private NameNormalizer $nameNormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nameNormalizer = new NameNormalizer();
    }

    /**
     * @throws Throwable
     */
    public function testMandatoryInterfaceSuffixNormalizationSucceeds(): void
    {
        self::assertSame(
            ['channel'],
            $this->nameNormalizer->normalize('App\Domain\ChannelInterface'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testMandatoryAbstractSuffixNormalizationSucceeds(): void
    {
        self::assertSame(
            ['order'],
            $this->nameNormalizer->normalize('OrderAbstract'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testMandatoryAbstractPrefixNormalizationSucceeds(): void
    {
        self::assertSame(
            ['serviceMessage'],
            $this->nameNormalizer->normalize('App\Service\AbstractServiceMessage'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testMandatoryAbstractPrefixWithInterfaceSuffixNormalizationSucceeds(): void
    {
        self::assertSame(
            ['foo'],
            $this->nameNormalizer->normalize('AbstractFooInterface'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testAbstractWordEmbeddedInNameIsNotStrippedAsPrefix(): void
    {
        self::assertSame(
            ['abstractly'],
            $this->nameNormalizer->normalize('Abstractly'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testMandatoryTraitSuffixNormalizationSucceeds(): void
    {
        self::assertSame(
            ['loggerAware'],
            $this->nameNormalizer->normalize('App\LoggerAwareTrait'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testOptionalDtoSuffixNormalizationSucceeds(): void
    {
        self::assertSame(
            ['userDto', 'user'],
            $this->nameNormalizer->normalize('UserDto'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testOptionalEntitySuffixNormalizationSucceeds(): void
    {
        self::assertSame(
            ['orderEntity', 'order'],
            $this->nameNormalizer->normalize('OrderEntity'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testNeverStripFactorySuffixNormalizationSucceeds(): void
    {
        self::assertSame(
            ['userFactory'],
            $this->nameNormalizer->normalize('UserFactory'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testNeverStripCollectionSuffixNormalizationSucceeds(): void
    {
        self::assertSame(
            ['nodeCollection'],
            $this->nameNormalizer->normalize('NodeCollection'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testInitialismNormalizationSucceeds(): void
    {
        self::assertSame(
            ['urlParserDto', 'urlParser'],
            $this->nameNormalizer->normalize('Acme\URLParserDto'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testMandatorySuffixNormalizationWithEmptyStrippedNameSucceeds(): void
    {
        self::assertSame(
            ['interface'],
            $this->nameNormalizer->normalize('Interface'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testOptionalSuffixNormalizationWithEmptyStrippedNameSucceeds(): void
    {
        self::assertSame(
            ['dto'],
            $this->nameNormalizer->normalize('Dto'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testEmptyClassNameNormalizationSucceeds(): void
    {
        self::assertSame(
            [''],
            $this->nameNormalizer->normalize(''),
        );
    }

    /**
     * @throws Throwable
     */
    public function testNonWordCharacterOnlyNormalizationSucceeds(): void
    {
        self::assertSame(
            ['_'],
            $this->nameNormalizer->normalize('_'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testNumericSegmentNormalizationSucceeds(): void
    {
        self::assertSame(
            ['node2D'],
            $this->nameNormalizer->normalize('Node2D'),
        );
    }
}
