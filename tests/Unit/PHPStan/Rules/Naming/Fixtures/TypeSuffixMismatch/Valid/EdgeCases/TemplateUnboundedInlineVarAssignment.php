<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

/**
 * @template TConnection
 *
 * @param TConnection $source
 */
function runTemplateUnboundedInlineVarAssignment(mixed $source): void
{
    /** @var TConnection $connection */
    $connection = $source;
}
