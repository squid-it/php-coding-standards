<?php

declare(strict_types=1);

namespace LoggerContextKeyCamelCaseFixtures\Valid;

use Psr\Log\LoggerInterface;

final class ValidScenarios
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function run(string $dynamicKey): void
    {
        $this->logger->info('saved', ['fooBar' => 1, 'userId' => 2]);
        $this->logger->log('info', 'saved', ['eventName' => 'store']);
        $this->logger->warning('saved', [$dynamicKey => 1]);

        $localLogger = new LocalLogger();
        $localLogger->info('saved', ['foo_bar' => 1]);
    }
}
