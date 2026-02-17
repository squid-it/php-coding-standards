<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Fixtures\SingleClassPerFile\Valid;

class ClassWithAnonymousClass
{
    public function create(): object
    {
        return new class {};
    }
}
