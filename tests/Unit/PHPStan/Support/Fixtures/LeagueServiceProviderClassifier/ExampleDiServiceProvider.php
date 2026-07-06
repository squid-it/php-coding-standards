<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\LeagueServiceProviderClassifier;

use League\Container\ServiceProvider\AbstractServiceProvider;

final class ExampleDiServiceProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return false;
    }

    public function register(): void {}
}
