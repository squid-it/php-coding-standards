<?php

declare(strict_types=1);

namespace LoggerContextKeyCamelCaseFixtures\Invalid;

use Psr\Log\LoggerInterface;

final class InvalidScenarios
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function run(): void
    {
        $this->logger->info('saved', ['foo_bar' => 1, 'BarKey' => 2]);
        $this->logger->log('info', 'saved', ['user_id' => 3]);
    }
}
