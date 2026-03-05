<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\EdgeCases\TemplateHierarchy\GenericConnectionInterface;
use TypeSuffixMismatchFixtures\Valid\FooData;

/**
 * @template TConnection of FooData
 * @implements GenericConnectionInterface<TConnection>
 */
final class ImplementsTemplateBoundToConcreteTypedPropertyMismatch implements GenericConnectionInterface
{
    /** @var TConnection */
    private object $connection;
}
