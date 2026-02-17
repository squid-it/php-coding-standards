<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Fixtures\DisallowAnonymousFunction\Invalid;

$arr = [1, 2, 3];

$filtered = array_filter($arr, fn (int $x): bool => $x > 1);
