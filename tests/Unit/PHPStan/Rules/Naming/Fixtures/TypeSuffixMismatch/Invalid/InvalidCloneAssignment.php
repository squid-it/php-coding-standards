<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid;

function runInvalidCloneAssignment(FooData $fooData): void
{
    $item = clone $fooData;
}
