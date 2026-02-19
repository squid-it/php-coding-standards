<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime;

final class RuntimeIslandService
{
    public function __construct(
        public readonly string $serviceId,
    ) {}

    public function island(): string
    {
        return $this->serviceId;
    }
}
