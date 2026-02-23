<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerInterface;
use TypeSuffixMismatchFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerMasonInterface;

final class IntersectionTypedPropertyDocblockNarrowsType
{
    /** @var ContainerMasonInterface&ContainerInterface */
    private ContainerInterface $containerMason;
}
