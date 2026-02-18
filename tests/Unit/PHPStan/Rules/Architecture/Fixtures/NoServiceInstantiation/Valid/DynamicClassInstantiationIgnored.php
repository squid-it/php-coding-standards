<?php

declare(strict_types=1);

namespace NoServiceInstantiationFixtures\Valid;

final class DynamicClassInstantiationIgnored
{
    /**
     * @param class-string<object> $className
     */
    public function run(string $className): object
    {
        return new $className();
    }
}
