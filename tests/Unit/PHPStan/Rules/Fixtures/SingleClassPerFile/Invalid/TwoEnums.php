<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Fixtures\SingleClassPerFile\Invalid;

enum TwoEnumsFirst: string
{
    case Example = 'example';
}

enum TwoEnumsSecond: string
{
    case Example = 'example';
}
