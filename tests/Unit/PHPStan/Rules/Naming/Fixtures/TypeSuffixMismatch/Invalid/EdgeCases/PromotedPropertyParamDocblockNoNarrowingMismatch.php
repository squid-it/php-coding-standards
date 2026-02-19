<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerInterface;

final class PromotedPropertyParamDocblockNoNarrowingMismatch
{
    /**
     * @param ContainerInterface $containerMason
     */
    public function __construct(
        private ContainerInterface $containerMason,
    ) {}
}
