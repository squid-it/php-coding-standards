<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Fixtures\DisallowLogicalNot\Valid;

$value = null;
$isNotNull = $value !== null;
$isNull = $value === null;
