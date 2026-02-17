<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Fixtures\DisallowAnonymousFunction\Invalid;

$fn = function (): string {
    return 'anonymous';
};
