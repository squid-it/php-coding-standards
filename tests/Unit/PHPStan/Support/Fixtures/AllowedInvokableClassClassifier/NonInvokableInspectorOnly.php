<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\AllowedInvokableClassClassifier;

final class NonInvokableInspectorOnly
{
    public function __construct(
        private bool $hasError = false,
    ) {}

    public function getLastError(): ?string
    {
        if ($this->hasError === true) {
            return 'failed';
        }

        return null;
    }
}
