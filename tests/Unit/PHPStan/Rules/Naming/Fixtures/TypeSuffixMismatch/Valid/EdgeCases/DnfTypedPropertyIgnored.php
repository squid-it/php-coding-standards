<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\BarData;
use TypeSuffixMismatchFixtures\Valid\ChannelInterface;
use TypeSuffixMismatchFixtures\Valid\FooData;

final class DnfTypedPropertyIgnored
{
    private (FooData&ChannelInterface)|BarData $service;
}
