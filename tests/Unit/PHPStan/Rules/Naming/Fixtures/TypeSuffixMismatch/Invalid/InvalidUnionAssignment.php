<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid;

function runInvalidUnionAssignment(FooData|BarData $fooDataOrBarData): void
{
    $item = $fooDataOrBarData;
}
