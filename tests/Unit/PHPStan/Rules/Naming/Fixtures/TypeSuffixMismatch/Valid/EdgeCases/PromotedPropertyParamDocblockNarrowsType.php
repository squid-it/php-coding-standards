<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerInterface;
use TypeSuffixMismatchFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerMasonInterface;

final class PromotedPropertyParamDocblockNarrowsType
{
    /**
     * @param ContainerMasonInterface $containerMason
     */
    public function __construct(
        private ContainerInterface $containerMason,
    ) {}
}
