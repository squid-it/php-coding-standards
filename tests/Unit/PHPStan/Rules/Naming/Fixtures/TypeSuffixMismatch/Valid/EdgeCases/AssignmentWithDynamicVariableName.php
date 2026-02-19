<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\FooData;

function runAssignmentWithDynamicVariableName(FooData $fooData, string $variableName): void
{
    ${$variableName} = $fooData;
}
