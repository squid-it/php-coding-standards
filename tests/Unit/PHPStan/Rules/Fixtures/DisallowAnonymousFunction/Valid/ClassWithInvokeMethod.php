<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Fixtures\DisallowAnonymousFunction\Valid;

class ClassWithInvokeMethod
{
    public function __invoke(): string
    {
        return 'invoked';
    }
}
