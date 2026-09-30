<?php

declare(strict_types=1);

namespace MultilinePromotedPropertiesFixtures;

final class NoPromotedProperties
{
    public function __construct(Clock $clock, Logger $logger) {}

    public function run(int $first, int $second): void {}
}
