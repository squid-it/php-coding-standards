<?php

declare(strict_types=1);

namespace IterablePluralNamingFixtures\Valid;

final class ValidScenarios
{
    public function run(Node $node): void
    {
        $nodes          = [$node];
        $nodeList       = [$node];
        $activeNodeList = [$node];
        $nodeById       = ['primary' => $node];
        $nodeLookup     = ['primary' => $node];
        $nodeByKey      = ['primary' => $node];

        $nodes[] = new Node();
    }
}
