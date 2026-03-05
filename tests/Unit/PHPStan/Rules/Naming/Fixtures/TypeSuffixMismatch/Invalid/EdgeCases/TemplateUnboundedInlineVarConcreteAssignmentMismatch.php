<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\FooData;

/**
 * @template TConnection
 *
 * @param TConnection $source
 */
function runTemplateUnboundedInlineVarConcreteAssignmentMismatch(mixed $source): void
{
    /** @var TConnection $connection */
    $connection = new FooData();
}
