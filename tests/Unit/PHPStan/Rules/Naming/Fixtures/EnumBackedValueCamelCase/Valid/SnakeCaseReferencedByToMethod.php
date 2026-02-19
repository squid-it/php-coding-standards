<?php

declare(strict_types=1);

namespace EnumBackedValueCamelCaseFixtures\Valid;

enum SnakeCaseReferencedByToMethod: string
{
    case FooBar = 'foo_bar';

    public function toDb(): string
    {
        return match ($this) {
            self::FooBar => 'foo_bar',
        };
    }
}
