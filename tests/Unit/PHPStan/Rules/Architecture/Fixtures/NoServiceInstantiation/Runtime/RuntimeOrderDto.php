<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime;

readonly class RuntimeOrderDto
{
    public function __construct(
        private string $orderId,
    ) {}

    public function getOrderId(): string
    {
        return $this->orderId;
    }
}
