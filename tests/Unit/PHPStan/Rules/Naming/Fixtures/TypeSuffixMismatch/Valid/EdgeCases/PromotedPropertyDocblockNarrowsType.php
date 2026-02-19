<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerInterface;
use TypeSuffixMismatchFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerMasonInterface;

final class PromotedPropertyDocblockNarrowsType
{
    public function __construct(
        /** @var ContainerMasonInterface $containerMason */
        private ContainerInterface $containerMason,
    ) {}
}
