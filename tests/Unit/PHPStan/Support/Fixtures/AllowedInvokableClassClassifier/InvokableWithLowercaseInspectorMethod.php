<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\AllowedInvokableClassClassifier;

final class InvokableWithLowercaseInspectorMethod
{
    public function __construct(
        private bool $hasValue = false,
    ) {}

    public function __invoke(): void {}

    public function getter(): ?string
    {
        if ($this->hasValue === true) {
            return 'value';
        }

        return null;
    }
}
