<?php

declare(strict_types=1);

namespace Fixture\DisallowLogicalNot\Valid;

$value  = random_int(0, 1) === 1;
$result = $value === false;
