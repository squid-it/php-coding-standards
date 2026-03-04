<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\FooData;

/**
 * @template TConnection of FooData
 *
 * @param TConnection $source
 */
function runTemplateConcreteBoundInlineVarAssignmentMismatch(FooData $source): void
{
    /** @var TConnection $connection */
    $connection = $source;
}
