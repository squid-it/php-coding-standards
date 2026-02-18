<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid;

class FooData {}

class BarData {}

function runInvalidUnionAssignment(FooData|BarData $fooDataOrBarData): void
{
    $item = $fooDataOrBarData;
}
