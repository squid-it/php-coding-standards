<?php

declare(strict_types=1);

namespace NoServiceInstantiationFixtures\Valid;

readonly class OrderDto
{
    public function __construct(
        private string $orderId,
    ) {}

    public function getOrderId(): string
    {
        return $this->orderId;
    }
}
