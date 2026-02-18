<?php

declare(strict_types=1);

namespace Fixture\DisallowAnonymousFunction\Invalid;

$arr = [3, 1, 2];

usort($arr, function (int $a, int $b): int {
    return $a - $b;
});
