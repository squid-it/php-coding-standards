<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Testing\PHPStanTestCase;
use PHPUnit\Framework\MockObject\Stub;
use SquidIT\PhpCodingStandards\PHPStan\Support\InterfaceAvailabilityCheckerInterface;
use SquidIT\PhpCodingStandards\PHPStan\Support\LeagueServiceProviderClassifier;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\LeagueServiceProviderClassifier\ExampleDiServiceProvider;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\LeagueServiceProviderClassifier\PlainContainerConfigurator;
use Throwable;

final class LeagueServiceProviderClassifierTest extends PHPStanTestCase
{
    private ReflectionProvider $reflectionProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reflectionProvider = self::createReflectionProvider();
    }

    /**
     * @throws Throwable
     */
    public function testIsLeagueServiceProviderClassForServiceProviderSucceeds(): void
    {
        $leagueServiceProviderClassifier = new LeagueServiceProviderClassifier();
        $classReflection                 = $this->reflectionProvider->getClass(ExampleDiServiceProvider::class);

        self::assertTrue($leagueServiceProviderClassifier->isLeagueServiceProviderClass($classReflection));
    }

    /**
     * @throws Throwable
     */
    public function testIsLeagueServiceProviderClassForPlainClassReturnsFalseSucceeds(): void
    {
        $leagueServiceProviderClassifier = new LeagueServiceProviderClassifier();
        $classReflection                 = $this->reflectionProvider->getClass(PlainContainerConfigurator::class);

        self::assertFalse($leagueServiceProviderClassifier->isLeagueServiceProviderClass($classReflection));
    }

    /**
     * @throws Throwable
     */
    public function testIsLeagueServiceProviderClassWhenInterfaceUnavailableReturnsFalseSucceeds(): void
    {
        /** @var InterfaceAvailabilityCheckerInterface&Stub $interfaceAvailabilityChecker */
        $interfaceAvailabilityChecker = self::createStub(InterfaceAvailabilityCheckerInterface::class);
        $interfaceAvailabilityChecker->method('isInterfaceAvailable')->willReturn(false);

        $leagueServiceProviderClassifier = new LeagueServiceProviderClassifier($interfaceAvailabilityChecker);
        $classReflection                 = $this->reflectionProvider->getClass(ExampleDiServiceProvider::class);

        self::assertFalse($leagueServiceProviderClassifier->isLeagueServiceProviderClass($classReflection));
    }
}
