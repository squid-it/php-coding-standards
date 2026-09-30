<?php

declare(strict_types=1);

namespace MultilinePromotedPropertiesFixtures;

final readonly class MixedPromotedAndPlainParameters
{
    public function __construct(
        private Clock $clock,
        Logger $logger,
        int $timeoutInSeconds = 30,
    ) {}
}
