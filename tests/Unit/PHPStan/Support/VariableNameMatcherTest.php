<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support;

use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PHPStan\Support\VariableNameMatcher;
use Throwable;

final class VariableNameMatcherTest extends TestCase
{
    private VariableNameMatcher $variableNameMatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->variableNameMatcher = new VariableNameMatcher();
    }

    /**
     * @throws Throwable
     */
    public function testIsValidWithExactBaseNameSucceeds(): void
    {
        self::assertTrue($this->variableNameMatcher->isValid('channel', 'channel'));
    }

    /**
     * @throws Throwable
     */
    public function testIsValidWithPrefixedBaseNameSuffixSucceeds(): void
    {
        self::assertTrue($this->variableNameMatcher->isValid('readChannel', 'channel'));
    }

    /**
     * @throws Throwable
     */
    public function testIsValidWithUnrelatedVariableNameFails(): void
    {
        self::assertFalse($this->variableNameMatcher->isValid('item', 'channel'));
    }

    /**
     * @throws Throwable
     */
    public function testIsValidWithEmptyBaseNameFails(): void
    {
        self::assertFalse($this->variableNameMatcher->isValid('channel', ''));
    }
}
