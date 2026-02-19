<?php

declare(strict_types=1);

namespace NoServiceInstantiationFixtures\Valid;

final class HttpClientFactory
{
    public function create(): FactoryCreatedService
    {
        return new FactoryCreatedService();
    }
}
