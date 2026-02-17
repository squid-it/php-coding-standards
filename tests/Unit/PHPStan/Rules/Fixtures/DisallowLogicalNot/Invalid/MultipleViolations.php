<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Fixtures\DisallowLogicalNot\Invalid;

$a = true;
$b = false;

$result1 = !$a;
$result2 = !$b;

if (!$a) {
    $b = true;
}
