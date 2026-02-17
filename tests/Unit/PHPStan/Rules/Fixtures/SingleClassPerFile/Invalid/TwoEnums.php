<?php

declare(strict_types=1);

namespace Fixture\SingleClassPerFile\Invalid;

enum TwoEnumsFirst: string
{
    case Example = 'example';
}

enum TwoEnumsSecond: string
{
    case Example = 'example';
}
