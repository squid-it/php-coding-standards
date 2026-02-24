<?php

declare(strict_types=1);

namespace ReadonlyClassPromotionFixtures\Invalid;

final class FinalClassWithReadonlyProperties
{
    public readonly int $id;
    private readonly string $name;
}


