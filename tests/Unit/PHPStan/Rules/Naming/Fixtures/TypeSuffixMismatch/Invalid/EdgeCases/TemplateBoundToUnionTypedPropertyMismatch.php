<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\BarData;
use TypeSuffixMismatchFixtures\Valid\FooData;

/**
 * @template TConnection of FooData|BarData
 */
final class TemplateBoundToUnionTypedPropertyMismatch
{
    /** @var TConnection */
    private object $connection;
}
