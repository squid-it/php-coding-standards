<?php

declare(strict_types=1);

namespace DisallowCountInForConditionFixtures\Invalid\CountCalls;

use Countable;

/**
 * @param array<int, int> $values
 */
function checkConditions(array $values, Countable $countable): void
{
    for ($index = 0; $index < count($values); ++$index) {
        echo $index;
    }

    for ($index = 0; $index < \count($values); ++$index) {
        echo $index;
    }

    for ($index = 0; $index < CoUnT($values); ++$index) {
        echo $index;
    }

    for ($index = 0; $index < max(1, count($values)); ++$index) {
        echo $index;
    }

    for ($index = 0; $index < 10 && $index < count($values); ++$index) {
        echo $index;
    }

    for ($index = 0; count($values), $index < 10; ++$index) {
        echo $index;
    }

    for ($index = 0; $index < count($values) + count($values); ++$index) {
        echo $index;
    }

    for (
        $index = 0;
        $index < count($values);
        ++$index
    ) {
        echo $index;
    }

    for ($outer = 0; $outer < 2; ++$outer) {
        for ($index = 0; $index < count($values); ++$index) {
            echo $index;
        }
    }

    for ($index = 0; $index < count($countable); ++$index) {
        echo $index;
    }
}
