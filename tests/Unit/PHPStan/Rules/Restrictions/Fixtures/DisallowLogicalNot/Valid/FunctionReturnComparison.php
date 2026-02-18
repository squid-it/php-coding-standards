<?php

declare(strict_types=1);

namespace Fixture\DisallowLogicalNot\Valid;

$arr   = [1, 2, random_int(3, 4)];
$found = in_array(4, $arr, true) === false;

$value  = random_int(0, 1) === 1 ? null : 'value';
$isNull = is_null($value) === true;
