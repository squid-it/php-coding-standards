<?php

declare(strict_types=1);

namespace Fixture\DisallowLogicalNot\Invalid;

$a = true;
$b = false;

$result1 = !$a;
$result2 = !$b;

if (!$a) {
    $b = true;
}
