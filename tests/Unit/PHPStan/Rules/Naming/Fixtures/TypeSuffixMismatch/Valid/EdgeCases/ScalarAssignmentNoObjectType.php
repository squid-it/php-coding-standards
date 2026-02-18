<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

function runScalarAssignmentNoObjectType(): void
{
    $number = random_int(0, 1);
}
