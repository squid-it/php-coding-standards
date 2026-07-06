<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

use League\Container\ServiceProvider\ServiceProviderInterface;
use PHPStan\Reflection\ClassReflection;

final readonly class LeagueServiceProviderClassifier
{
    public function __construct(
        private InterfaceAvailabilityCheckerInterface $interfaceAvailabilityChecker = new InterfaceAvailabilityChecker(),
    ) {}

    public function isLeagueServiceProviderClass(ClassReflection $classReflection): bool
    {
        if ($this->interfaceAvailabilityChecker->isInterfaceAvailable(ServiceProviderInterface::class) === false) {
            return false;
        }

        return $classReflection->isSubclassOf(ServiceProviderInterface::class);
    }
}
