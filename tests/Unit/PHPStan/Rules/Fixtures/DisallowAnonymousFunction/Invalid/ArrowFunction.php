<?php

declare(strict_types=1);

namespace Fixture\DisallowAnonymousFunction\Invalid;

$fn = fn (): string => 'arrow';
