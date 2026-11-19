<?php

declare(strict_types=1);

namespace DisallowCountInForConditionFixtures\Valid\CountOutsideConditions;

/**
 * @param array<int, int> $values
 */
function checkOtherPositions(array $values): void
{
    for ($index = 0, $valueCount = count($values); $index < $valueCount; ++$index) {
        echo count($values);
    }

    $valueCount = count($values);
    for ($index = 0; $index < $valueCount; $index += count($values)) {
        echo $index;
    }

    for ($index = 0; ; ++$index) {
        if ($index >= count($values)) {
            break;
        }
    }

    while (count($values) > 0) {
        array_pop($values);
    }

    do {
        array_pop($values);
    } while (count($values) > 0);
}
