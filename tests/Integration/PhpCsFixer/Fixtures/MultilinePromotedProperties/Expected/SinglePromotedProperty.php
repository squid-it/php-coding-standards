<?php

declare(strict_types=1);

namespace MultilinePromotedPropertiesFixtures;

final readonly class SinglePromotedProperty
{
    public function __construct(
        private ArmedTestTimeout $armedTestTimeout,
    ) {}
}
