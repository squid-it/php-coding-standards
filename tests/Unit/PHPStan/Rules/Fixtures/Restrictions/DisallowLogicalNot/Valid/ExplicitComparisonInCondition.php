<?php

declare(strict_types=1);

namespace Fixture\DisallowLogicalNot\Valid;

$value = true;
$running = true;

if ($value === false) {
    $value = true;
}

while ($running === true) {
    $running = false;
}
