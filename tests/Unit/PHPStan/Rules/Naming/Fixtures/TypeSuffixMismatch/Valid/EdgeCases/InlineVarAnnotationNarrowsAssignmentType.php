<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerInterface;
use TypeSuffixMismatchFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerMasonInterface;

function runInlineVarAnnotationNarrowsAssignmentType(ContainerInterface $container): void
{
    /** @var ContainerMasonInterface $containerMason */
    $containerMason = $container;
}
