<?php

declare(strict_types=1);

namespace LoopValueVariableNamingFixtures\Invalid;

final class InvalidExpressionFallbackValueName
{
    /**
     * @param array<int, ChildNode> $children
     */
    public function run(array $children): void
    {
        foreach ($this->resolveChildren($children) as $element) {
            $element->touch();
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
