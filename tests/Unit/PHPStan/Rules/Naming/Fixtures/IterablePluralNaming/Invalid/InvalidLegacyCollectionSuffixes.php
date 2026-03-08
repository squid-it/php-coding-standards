<?php

declare(strict_types=1);

namespace IterablePluralNamingFixtures\Invalid;

final class InvalidLegacyCollectionSuffixes
{
    public function run(Node $node): void
    {
        $nodeById       = ['primary' => $node];
        $nodeByKey      = ['primary' => $node];
        $nodeLookup     = ['primary' => $node];
        $nodeCollection = ['primary' => $node];
    }
}
