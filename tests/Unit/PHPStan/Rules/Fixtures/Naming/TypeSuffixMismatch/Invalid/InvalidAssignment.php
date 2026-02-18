<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid;

class FooData {}

function runInvalidAssignment(): void
{
    $item = new FooData();
}
