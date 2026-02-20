<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerInterface;
use TypeSuffixMismatchFixtures\Valid\EdgeCases\InlineVarAnnotation\ContainerMasonInterface;

readonly class PromotedPropertyParamDocblockParentInterfaceNameValid
{
    /**
     * @param ContainerMasonInterface|null $container
     */
    public function __construct(
        public ?ContainerInterface $container = null,
    ) {}
}
