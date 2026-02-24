<?php

declare(strict_types=1);

namespace ReadonlyClassPromotionFixtures\Invalid;

final class FinalClassWithReadonlyAndPromotedReadonlyProperties
{
    public readonly int $id;

    public function __construct(
        private readonly string $name,
    ) {}
}


