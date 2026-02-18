<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime;

final class RuntimeInheritedMutableService extends RuntimeMutableBase
{
    public function getServiceId(): string
    {
        return 'service';
    }
}
