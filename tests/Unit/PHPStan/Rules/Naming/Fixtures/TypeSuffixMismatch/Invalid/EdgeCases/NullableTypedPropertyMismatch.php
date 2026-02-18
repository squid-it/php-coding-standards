<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid\EdgeCases;

use TypeSuffixMismatchFixtures\Invalid\FooData;

final class NullableTypedPropertyMismatch
{
    private ?FooData $service;
}
