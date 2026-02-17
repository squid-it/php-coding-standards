<?php

declare(strict_types=1);

namespace Fixture\DisallowLogicalNot\Invalid;

class Checker
{
    public function isValid(): bool
    {
        return true;
    }
}

$checker = new Checker();
$result = !$checker->isValid();
