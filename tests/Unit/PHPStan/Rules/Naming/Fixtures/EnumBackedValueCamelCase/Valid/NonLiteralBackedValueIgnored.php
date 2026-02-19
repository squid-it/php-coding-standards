<?php

declare(strict_types=1);

namespace EnumBackedValueCamelCaseFixtures\Valid;

enum NonLiteralBackedValueIgnored: string
{
    private const string FOO_BAR = 'foo_bar';

    case FooBar = self::FOO_BAR;
}
