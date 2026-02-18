<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid;

class FooData {}

function runInvalidCloneAssignment(FooData $fooData): void
{
    $item = clone $fooData;
}
