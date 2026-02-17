<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Fixtures\DisallowAnonymousFunction\Invalid;

$prefix = 'hello';

$fn = function (string $name) use ($prefix): string {
    return $prefix . ' ' . $name;
};
