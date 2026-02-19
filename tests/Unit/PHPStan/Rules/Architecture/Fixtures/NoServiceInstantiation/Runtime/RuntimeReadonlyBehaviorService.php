<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime;

final readonly class RuntimeReadonlyBehaviorService
{
    public function __construct(
        private string $serviceId,
    ) {}

    public function execute(): void
    {
        if ($this->serviceId === '') {
            return;
        }
    }
}
