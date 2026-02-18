<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid;

function runInvalidAssignment(): void
{
    $item = new FooData();
}
