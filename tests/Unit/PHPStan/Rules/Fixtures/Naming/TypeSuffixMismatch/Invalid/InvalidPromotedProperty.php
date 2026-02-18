<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid;

class FooData {}

final class InvalidPromotedProperty
{
    public function __construct(
        private FooData $service,
    ) {}
}
