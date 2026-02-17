<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Fixtures\DisallowLogicalNot\Valid;

$value = true;
$running = true;

if ($value === false) {
    $value = true;
}

while ($running === true) {
    $running = false;
}
