<?php

declare(strict_types=1);

namespace LoggerContextKeyCamelCaseFixtures\Valid;

final class LocalLogger
{
    /**
     * @param array<string, mixed> $context
     */
    public function info(string $message, array $context = []): void
    {
    }
}
