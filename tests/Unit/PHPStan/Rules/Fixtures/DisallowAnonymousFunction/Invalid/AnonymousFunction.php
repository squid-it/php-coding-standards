<?php

declare(strict_types=1);

namespace Fixture\DisallowAnonymousFunction\Invalid;

$fn = function (): string {
    return 'anonymous';
};
