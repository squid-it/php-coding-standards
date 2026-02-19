<?php

declare(strict_types=1);

namespace Fixture\DisallowAnonymousFunction\Valid;

class ClassWithInvokeMethod
{
    public function __invoke(): string
    {
        return 'invoked';
    }
}
