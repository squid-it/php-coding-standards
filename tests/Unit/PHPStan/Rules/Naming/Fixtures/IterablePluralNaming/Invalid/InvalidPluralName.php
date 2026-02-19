<?php

declare(strict_types=1);

namespace IterablePluralNamingFixtures\Invalid;

final class InvalidPluralName
{
    public function run(Node $node): void
    {
        $itemList = [$node];
    }
}
