<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

final readonly class InterfaceAvailabilityChecker implements InterfaceAvailabilityCheckerInterface
{
    public function isInterfaceAvailable(string $interfaceName): bool
    {
        return interface_exists($interfaceName);
    }
}
