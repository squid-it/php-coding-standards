<?php

declare(strict_types=1);

namespace Fixture\DisallowAnonymousFunction\Valid;

class RegularClassWithMethods
{
    public function doSomething(): string
    {
        return 'something' . $this->helper();
    }

    private function helper(): int
    {
        return 42;
    }
}
