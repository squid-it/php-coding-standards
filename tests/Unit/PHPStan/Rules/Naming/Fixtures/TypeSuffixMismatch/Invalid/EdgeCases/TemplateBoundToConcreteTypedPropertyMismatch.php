<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\FooData;

/**
 * @template TConnection of FooData
 */
final class TemplateBoundToConcreteTypedPropertyMismatch
{
    /** @var TConnection */
    private object $connection;
}
