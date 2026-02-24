<?php

declare(strict_types=1);

namespace ReadonlyClassPromotionFixtures\Valid;

final class FinalClassWithMutableProperty
{
    public readonly int $id;
    private string $name;
}


