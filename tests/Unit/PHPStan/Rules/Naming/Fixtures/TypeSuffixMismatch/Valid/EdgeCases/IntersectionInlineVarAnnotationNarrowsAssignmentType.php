<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerInterface;
use TypeSuffixMismatchFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerMasonInterface;

function runIntersectionInlineVarAnnotationNarrowsAssignmentType(ContainerInterface $container): void
{
    /** @var ContainerMasonInterface&ContainerInterface $containerMason */
    $containerMason = $container;
}
