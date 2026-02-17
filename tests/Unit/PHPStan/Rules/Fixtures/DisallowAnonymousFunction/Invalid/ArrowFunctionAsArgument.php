<?php

declare(strict_types=1);

namespace Fixture\DisallowAnonymousFunction\Invalid;

$arr = [1, 2, 3];

$filtered = array_filter($arr, fn (int $x): bool => $x > 1);
