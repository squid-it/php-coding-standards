<?php

declare(strict_types=1);

namespace Fixture\DisallowLogicalNot\Invalid;

$a = random_int(0, 1) === 1;
$b = random_int(0, 1) === 1;

$result1 = !$a;
$result2 = !$b;

if (!$a) {
    $b = true;
}
