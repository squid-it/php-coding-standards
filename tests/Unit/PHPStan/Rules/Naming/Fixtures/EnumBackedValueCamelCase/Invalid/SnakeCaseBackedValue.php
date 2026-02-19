<?php

declare(strict_types=1);

namespace EnumBackedValueCamelCaseFixtures\Invalid;

enum SnakeCaseBackedValue: string
{
    case FooBar = 'foo_bar';
}
