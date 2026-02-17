<?php

declare(strict_types=1);

namespace Fixture\SingleClassPerFile\Valid;

class ClassWithAnonymousClass
{
    public function create(): object
    {
        return new class {};
    }
}
