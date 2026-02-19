<?php

declare(strict_types=1);

namespace NoServiceInstantiationFixtures\Invalid;

final class DirectServiceInstantiation
{
    public function run(): HttpClient
    {
        return new HttpClient();
    }
}
