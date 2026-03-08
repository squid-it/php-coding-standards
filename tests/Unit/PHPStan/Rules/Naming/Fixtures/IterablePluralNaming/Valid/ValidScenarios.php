<?php

declare(strict_types=1);

namespace IterablePluralNamingFixtures\Valid;

final class ValidScenarios
{
    public function run(Node $node): void
    {
        $nodeList        = [$node];
        $activeNodeList  = [$node];
        $primaryNodeList = ['primary' => $node];

        $nodeList[] = new Node();
    }
}
