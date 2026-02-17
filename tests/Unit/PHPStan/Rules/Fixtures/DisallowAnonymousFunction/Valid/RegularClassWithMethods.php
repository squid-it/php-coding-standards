<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Fixtures\DisallowAnonymousFunction\Valid;

class RegularClassWithMethods
{
    public function doSomething(): string
    {
        return 'something';
    }

    private function helper(): int
    {
        return 42;
    }
}
