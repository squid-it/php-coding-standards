<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\AllowedInvokableClassClassifier;

final class InheritedInvokableStatusReporter extends AbstractInvokableStatusReporter
{
    public function isSuccessful(): bool
    {
        return $this->hasFailed() === false;
    }
}
