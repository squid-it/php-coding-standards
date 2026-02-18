<?php

declare(strict_types=1);

namespace IterablePluralNamingFixtures\Invalid;

final class InvalidMapName
{
    public function run(Node $node): void
    {
        $nodeMap = ['primary' => $node];
    }
}
