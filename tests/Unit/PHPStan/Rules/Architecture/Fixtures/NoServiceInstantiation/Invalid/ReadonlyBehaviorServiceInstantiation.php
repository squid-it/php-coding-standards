<?php

declare(strict_types=1);

namespace NoServiceInstantiationFixtures\Invalid;

final class ReadonlyBehaviorInstantiation
{
    public function run(): ReadonlyBehaviorService
    {
        return new ReadonlyBehaviorService('service-1');
    }
}
