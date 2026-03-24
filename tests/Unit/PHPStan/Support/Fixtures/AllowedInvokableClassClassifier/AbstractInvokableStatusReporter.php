<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\AllowedInvokableClassClassifier;

abstract class AbstractInvokableStatusReporter
{
    public function __construct(
        private bool $shouldFail = false,
        private ?string $lastError = null,
    ) {}

    public function __invoke(): void
    {
        if ($this->shouldFail === true) {
            $this->lastError = 'failed';

            return;
        }

        $this->lastError = null;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function hasFailed(): bool
    {
        return $this->lastError !== null;
    }
}
