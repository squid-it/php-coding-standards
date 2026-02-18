<?php

declare(strict_types=1);

namespace NoServiceInstantiationFixtures\Invalid;

final readonly class ReadonlyBehaviorService
{
    public function __construct(
        private string $id,
    ) {}

    public function execute(): void
    {
        $serviceId = $this->id;

        if ($serviceId === '') {
            return;
        }
    }
}
