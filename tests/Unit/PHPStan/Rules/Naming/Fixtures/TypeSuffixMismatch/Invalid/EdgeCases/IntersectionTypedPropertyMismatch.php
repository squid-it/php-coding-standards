<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid\EdgeCases;

use TypeSuffixMismatchFixtures\Invalid\ChannelInterface;
use TypeSuffixMismatchFixtures\Invalid\FooData;

final class IntersectionTypedPropertyMismatch
{
    private FooData&ChannelInterface $service;
}
