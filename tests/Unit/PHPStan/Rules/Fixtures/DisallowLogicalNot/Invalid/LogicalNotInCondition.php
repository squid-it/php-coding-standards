<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Fixtures\DisallowLogicalNot\Invalid;

$value = true;

if (!$value) {
    $value = false;
}
