<?php

declare(strict_types=1);

namespace LoopValueVariableNamingFixtures\Valid;

final class ValidScenarios
{
    /**
     * @param array<int, ChildNode> $children
     */
    public function run(array $children): void
    {
        foreach ($children as $child) {
            $child->touch();
        }

        foreach ($children as $childNode) {
            $childNode->touch();
        }

        foreach ($children as $firstChildNode) {
            $firstChildNode->touch();
        }

        foreach ($this->resolveChildren($children) as $prefixedChildNode) {
            $prefixedChildNode->touch();
        }
    }

    /**
     * @param array<int, ChildNode> $children
     *
     * @return array<int, ChildNode>
     */
    private function resolveChildren(array $children): array
    {
        return $children;
    }
}
