<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\BarData;
use TypeSuffixMismatchFixtures\Valid\FooData;

/**
 * @template TConnection of FooData|BarData
 *
 * @param TConnection $source
 */
function runTemplateUnionBoundInlineVarAssignmentMismatch(FooData|BarData $source): void
{
    /** @var TConnection $connection */
    $connection = $source;
}
