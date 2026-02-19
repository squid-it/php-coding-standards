<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support;

use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PHPStan\Support\Pluralizer;
use Throwable;

final class PluralizerTest extends TestCase
{
    private Pluralizer $pluralizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pluralizer = new Pluralizer();
    }

    /**
     * @throws Throwable
     */
    public function testCompanyPluralizationSucceeds(): void
    {
        self::assertSame('companies', $this->pluralizer->pluralize('company'));
    }

    /**
     * @throws Throwable
     */
    public function testClassPluralizationSucceeds(): void
    {
        self::assertSame('classes', $this->pluralizer->pluralize('class'));
    }

    /**
     * @throws Throwable
     */
    public function testDefaultPluralizationSucceeds(): void
    {
        self::assertSame('users', $this->pluralizer->pluralize('user'));
    }

    /**
     * @throws Throwable
     */
    public function testSibilantPluralizationSucceeds(): void
    {
        self::assertSame('boxes', $this->pluralizer->pluralize('box'));
        self::assertSame('brushes', $this->pluralizer->pluralize('brush'));
    }

    /**
     * @throws Throwable
     */
    public function testEmptyWordPluralizationSucceeds(): void
    {
        self::assertSame('', $this->pluralizer->pluralize(''));
    }
}
