<?php

declare(strict_types=1);

namespace IterablePluralNamingFixtures\Valid\EdgeCases;

use IterablePluralNamingFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerInterface;
use IterablePluralNamingFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerMasonInterface;

/**
 * @return array<int, ContainerMasonInterface>
 */
function runInlineVarAnnotationNarrowsIterableAssignmentType(ContainerInterface $container): array
{
    /** @var array<int, ContainerMasonInterface> $containerMasonList */
    $containerMasonList = [$container];

    return $containerMasonList;
}
