<?php

declare(strict_types=1);

namespace NoServiceInstantiationFixtures\Valid;

final class InternalClassInstantiationAllowed
{
    public function run(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
