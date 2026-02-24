<?php

declare(strict_types=1);

namespace ReadonlyClassPromotionFixtures\Valid;

final class FinalClassWithPromotedMutableProperty
{
    public function __construct(
        public readonly int $id,
        private string $name,
    ) {}
}


