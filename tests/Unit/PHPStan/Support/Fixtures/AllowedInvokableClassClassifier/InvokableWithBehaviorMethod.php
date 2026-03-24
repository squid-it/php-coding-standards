<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\AllowedInvokableClassClassifier;

final class InvokableWithBehaviorMethod
{
    public function __invoke(): void {}

    public function handle(): void {}
}
