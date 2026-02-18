<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid;

class FooData {}

final class InvalidTypedProperty
{
    private FooData $service;
}
