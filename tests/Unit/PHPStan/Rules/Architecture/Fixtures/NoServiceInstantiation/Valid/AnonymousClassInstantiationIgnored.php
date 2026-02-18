<?php

declare(strict_types=1);

namespace NoServiceInstantiationFixtures\Valid;

final class AnonymousClassInstantiationIgnored
{
    public function run(): object
    {
        return new class() {
        };
    }
}
