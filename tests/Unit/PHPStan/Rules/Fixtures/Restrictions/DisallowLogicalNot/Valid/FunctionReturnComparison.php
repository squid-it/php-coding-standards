<?php

declare(strict_types=1);

namespace Fixture\DisallowLogicalNot\Valid;

$arr = [1, 2, 3];
$found = in_array(4, $arr, true) === false;

$value = null;
$isNull = is_null($value) === true;
