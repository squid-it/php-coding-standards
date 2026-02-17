<?php

declare(strict_types=1);

namespace Fixture\SingleClassPerFile\Invalid;

class ClassAndEnumClass {}

enum ClassAndEnumEnum: string
{
    case Example = 'example';
}
