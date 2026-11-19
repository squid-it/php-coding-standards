<?php

declare(strict_types=1);

namespace DisallowCountInForConditionFixtures\Invalid\AliasedCount;

use function count as itemCount;

/**
 * @param array<int, int> $values
 */
function checkAlias(array $values): void
{
    for ($index = 0; $index < itemCount($values); ++$index) {
        echo $index;
    }
}
