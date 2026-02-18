<?php

declare(strict_types=1);

namespace EnumBackedValueCamelCaseFixtures\Invalid;

enum SnakeCaseWithDifferentToLiteral: string
{
    case FooBar = 'foo_bar';

    public function toDb(): string
    {
        return match ($this) {
            self::FooBar => 'fooBar',
        };
    }
}
