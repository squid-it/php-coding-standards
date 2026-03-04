<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

/**
 * @template TConnection of object
 *
 * @param TConnection $source
 */
function runTemplateObjectBoundInlineVarAssignment(object $source): void
{
    /** @var TConnection $connection */
    $connection = $source;
}
