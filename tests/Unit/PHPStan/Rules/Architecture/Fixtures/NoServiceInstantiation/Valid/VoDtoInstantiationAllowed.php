<?php

declare(strict_types=1);

namespace NoServiceInstantiationFixtures\Valid;

final class VoDtoInstantiationAllowed
{
    public function run(): OrderDto
    {
        return new OrderDto('order-1');
    }
}
