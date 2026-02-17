<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Fixtures\DisallowAnonymousFunction\Invalid;

$arr = [3, 1, 2];

usort($arr, function (int $a, int $b): int {
    return $a - $b;
});
