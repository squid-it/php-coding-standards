<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\AllowedInvokableClassClassifier;

final class InheritedInvokableWithBehaviorMethod extends AbstractInvokableWithBehaviorMethod
{
    public function __invoke(): void {}
}
