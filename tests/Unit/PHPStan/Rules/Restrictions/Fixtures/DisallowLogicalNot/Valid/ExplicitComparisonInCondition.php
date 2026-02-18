<?php

declare(strict_types=1);

namespace Fixture\DisallowLogicalNot\Valid;

$value   = random_int(0, 1) === 1;
$running = random_int(0, 1) === 1;

if ($value === false) {
    $value = true;
}

while ($running === true) {
    $running = false;
}
