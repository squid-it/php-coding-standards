<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime;

use League\Container\ServiceProvider\AbstractServiceProvider;

final class RuntimeExampleDiServiceProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return false;
    }

    public function register(): void
    {
        // A real provider instantiates its service definitions here; the fixture only needs to
        // implement ServiceProviderInterface (via AbstractServiceProvider) for reflection.
    }
}
