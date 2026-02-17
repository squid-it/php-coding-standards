<?php

declare(strict_types=1);

namespace Fixture\DisallowAnonymousFunction\Invalid;

$prefix = 'hello';

$fn = function (string $name) use ($prefix): string {
    return $prefix . ' ' . $name;
};
