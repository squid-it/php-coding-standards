<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support;

use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PHPStan\Support\TypeMessageDescriber;
use Throwable;

final class TypeMessageDescriberTest extends TestCase
{
    private TypeMessageDescriber $typeMessageDescriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeMessageDescriber = new TypeMessageDescriber();
    }

    /**
     * @throws Throwable
     */
    public function testDescribeTypeWithoutObjectClassNamesFallsBackToTypeDescriptionSucceeds(): void
    {
        self::assertSame('int', $this->typeMessageDescriber->describeType(new IntegerType()));
    }

    /**
     * @throws Throwable
     */
    public function testDescribeIterableValueTypeWithNonIterableFallsBackToDescribeTypeSucceeds(): void
    {
        self::assertSame('string', $this->typeMessageDescriber->describeIterableValueType(new StringType()));
    }
}
