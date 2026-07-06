<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

interface InterfaceAvailabilityCheckerInterface
{
    public function isInterfaceAvailable(string $interfaceName): bool;
}
