<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime;

final class RuntimeInvokableWithBehaviorMethod
{
    public function __invoke(): void
    {
    }

    public function execute(): void
    {
    }
}
