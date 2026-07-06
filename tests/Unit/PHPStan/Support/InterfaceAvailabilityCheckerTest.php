<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support;

use League\Container\ServiceProvider\ServiceProviderInterface;
use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PHPStan\Support\InterfaceAvailabilityChecker;
use Throwable;

final class InterfaceAvailabilityCheckerTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testIsInterfaceAvailableForExistingInterfaceSucceeds(): void
    {
        $interfaceAvailabilityChecker = new InterfaceAvailabilityChecker();

        self::assertTrue($interfaceAvailabilityChecker->isInterfaceAvailable(ServiceProviderInterface::class));
    }

    /**
     * @throws Throwable
     */
    public function testIsInterfaceAvailableForMissingInterfaceReturnsFalseSucceeds(): void
    {
        $interfaceAvailabilityChecker = new InterfaceAvailabilityChecker();

        self::assertFalse($interfaceAvailabilityChecker->isInterfaceAvailable('SquidIT\NonExistent\MissingInterface'));
    }
}
