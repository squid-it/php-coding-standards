<?php

declare(strict_types=1);

namespace MultilinePromotedPropertiesFixtures;

final class AsymmetricVisibilityPromotedProperty
{
    public function __construct(
        public private(set) Clock $clock,
    ) {}
}
