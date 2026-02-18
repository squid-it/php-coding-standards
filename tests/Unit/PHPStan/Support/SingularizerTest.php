<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support;

use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PHPStan\Support\Singularizer;
use Throwable;

final class SingularizerTest extends TestCase
{
    private Singularizer $singularizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->singularizer = new Singularizer();
    }

    /**
     * @throws Throwable
     */
    public function testCollectionSuffixStrippingSucceeds(): void
    {
        self::assertSame('node', $this->singularizer->singularize('nodeList'));
        self::assertSame('node', $this->singularizer->singularize('nodeCollection'));
        self::assertSame('node', $this->singularizer->singularize('nodeLookup'));
        self::assertSame('node', $this->singularizer->singularize('nodeById'));
        self::assertSame('node', $this->singularizer->singularize('nodeByKey'));
    }

    /**
     * @throws Throwable
     */
    public function testYPluralSingularizationSucceeds(): void
    {
        self::assertSame('company', $this->singularizer->singularize('companies'));
    }

    /**
     * @throws Throwable
     */
    public function testShortIesWordSingularizationSucceeds(): void
    {
        self::assertSame('tie', $this->singularizer->singularize('ties'));
    }

    /**
     * @throws Throwable
     */
    public function testEsPluralSingularizationSucceeds(): void
    {
        self::assertSame('class', $this->singularizer->singularize('classes'));
        self::assertSame('box', $this->singularizer->singularize('boxes'));
    }

    /**
     * @throws Throwable
     */
    public function testDefaultPluralSingularizationSucceeds(): void
    {
        self::assertSame('user', $this->singularizer->singularize('users'));
    }

    /**
     * @throws Throwable
     */
    public function testStripThenDepluralizeSingularizationSucceeds(): void
    {
        self::assertSame('company', $this->singularizer->singularize('companiesList'));
    }

    /**
     * @throws Throwable
     */
    public function testEmptyWordSingularizationSucceeds(): void
    {
        self::assertSame('', $this->singularizer->singularize(''));
    }
}
