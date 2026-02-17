<?php

declare(strict_types=1);

namespace Fixture\DisallowAnonymousFunction\Invalid;

$closure = function (): string {
    return 'closure';
};

$arrow = fn (): int => 42;

$arr = [3, 1, 2];

usort($arr, function (int $a, int $b): int {
    return $a - $b;
});
