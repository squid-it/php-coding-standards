<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

final class NoObjectTypedProperties
{
    /** @var mixed */
    private $rawValue;
    private int $count = 0;

    public function __construct(
        private int $index,
    ) {}
}
