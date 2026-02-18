<?php

declare(strict_types=1);

namespace Fixture\DisallowLogicalNot\Invalid;

$value = true;

if (!$value) {
    $value = false;
}
