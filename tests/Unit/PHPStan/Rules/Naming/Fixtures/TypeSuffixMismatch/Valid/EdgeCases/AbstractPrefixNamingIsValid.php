<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\AbstractServiceMessage;

final class AbstractPrefixNamingIsValid
{
    private AbstractServiceMessage $serviceMessage;

    public function __construct(
        private AbstractServiceMessage $initialServiceMessage,
    ) {
        $this->serviceMessage = $initialServiceMessage;
    }

    public function run(AbstractServiceMessage $serviceMessage): void
    {
        $activeServiceMessage = $serviceMessage;

        $this->serviceMessage = $activeServiceMessage;
    }
}
