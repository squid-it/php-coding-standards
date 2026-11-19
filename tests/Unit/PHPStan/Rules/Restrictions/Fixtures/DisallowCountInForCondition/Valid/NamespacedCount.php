<?php

declare(strict_types=1);

namespace DisallowCountInForConditionFixtures\Valid\NamespacedCount;

/**
 * @param array<int, int> $values
 */
function count(array $values): int
{
    return \count($values);
}

/**
 * @param array<int, int> $values
 */
function checkCustomFunction(array $values): void
{
    for ($index = 0; $index < count($values); ++$index) {
        echo $index;
    }

    for ($index = 0; $index < namespace\count($values); ++$index) {
        echo $index;
    }
}
