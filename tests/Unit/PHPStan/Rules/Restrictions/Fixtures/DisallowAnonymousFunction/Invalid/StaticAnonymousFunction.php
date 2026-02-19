<?php

declare(strict_types=1);

namespace Fixture\DisallowAnonymousFunction\Invalid;

$fn = static function (): string {
    return 'static anonymous';
};
