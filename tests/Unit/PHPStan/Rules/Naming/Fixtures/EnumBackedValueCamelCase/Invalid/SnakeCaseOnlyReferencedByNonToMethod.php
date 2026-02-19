<?php

declare(strict_types=1);

namespace EnumBackedValueCamelCaseFixtures\Invalid;

enum SnakeCaseOnlyReferencedByNonToMethod: string
{
    case FooBar = 'foo_bar';

    public function fromDb(): string
    {
        return match ($this) {
            self::FooBar => 'foo_bar',
        };
    }
}
