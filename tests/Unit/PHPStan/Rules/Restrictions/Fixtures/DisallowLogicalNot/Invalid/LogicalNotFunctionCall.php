<?php

declare(strict_types=1);

namespace Fixture\DisallowLogicalNot\Invalid;

$value  = random_int(0, 1) === 1 ? null : 'value';
$result = !is_null($value);
