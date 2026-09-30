<?php

declare(strict_types=1);

namespace MultilinePromotedPropertiesFixtures;

use SensitiveParameter;

final readonly class PromotedPropertyWithAttribute
{
    public function __construct(
        #[SensitiveParameter]
        private string $secret,
    ) {}
}
