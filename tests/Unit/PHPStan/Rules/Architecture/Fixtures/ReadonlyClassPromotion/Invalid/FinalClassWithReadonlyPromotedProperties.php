<?php

declare(strict_types=1);

namespace ReadonlyClassPromotionFixtures\Invalid;

final class FinalClassWithReadonlyPromotedProperties
{
    public function __construct(
        public readonly int $id,
        private readonly string $name,
    ) {}
}


