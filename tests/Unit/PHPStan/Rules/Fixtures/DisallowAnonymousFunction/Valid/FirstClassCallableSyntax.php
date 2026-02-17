<?php

declare(strict_types=1);

namespace Fixture\DisallowAnonymousFunction\Valid;

class FirstClassCallableSyntax
{
    public function getCallable(): \Closure
    {
        return $this->helper(...);
    }

    public function getStaticCallable(): \Closure
    {
        return self::staticHelper(...);
    }

    private function helper(): string
    {
        return 'helper';
    }

    private static function staticHelper(): string
    {
        return 'static';
    }
}
