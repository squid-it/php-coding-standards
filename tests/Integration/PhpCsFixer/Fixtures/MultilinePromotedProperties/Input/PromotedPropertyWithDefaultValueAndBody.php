<?php

declare(strict_types=1);

namespace MultilinePromotedPropertiesFixtures;

final class PromotedPropertyWithDefaultValueAndBody
{
    public function __construct(private readonly Clock $clock = new Clock(), private int $counter = 0)
    {
        $this->counter = $this->clock->now();
    }
}
