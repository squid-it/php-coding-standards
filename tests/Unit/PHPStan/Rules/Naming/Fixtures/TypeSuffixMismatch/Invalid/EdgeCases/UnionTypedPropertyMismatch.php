<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid\EdgeCases;

use TypeSuffixMismatchFixtures\Invalid\BarData;
use TypeSuffixMismatchFixtures\Invalid\FooData;

final class UnionTypedPropertyMismatch
{
    private FooData|BarData $service;
}
