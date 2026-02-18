<?php

declare(strict_types=1);

namespace LoopValueVariableNamingFixtures\Invalid;

final class InvalidValueName
{
    /**
     * @param array<int, ChildNode> $children
     */
    public function run(array $children): void
    {
        foreach ($children as $item) {
            $item->touch();
        }
    }
}
