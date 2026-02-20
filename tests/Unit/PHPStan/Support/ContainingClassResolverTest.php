<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support;

use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PHPStan\Support\ContainingClassResolver;
use Throwable;

final class ContainingClassResolverTest extends TestCase
{
    private ContainingClassResolver $containingClassResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->containingClassResolver = new ContainingClassResolver();
    }

    /**
     * @throws Throwable
     */
    public function testIsFactoryClassNameWithFactorySuffixSucceeds(): void
    {
        self::assertTrue(
            $this->containingClassResolver->isFactoryClassName('App\Service\OrderFactory'),
        );
    }

    /**
     * @throws Throwable
     */
    public function testIsAllowedCreatorClassNameSkipsEmptySuffixAndReturnsFalseSucceeds(): void
    {
        self::assertFalse(
            $this->containingClassResolver->isAllowedCreatorClassName(
                className: 'App\Service\OrderAssembler',
                allowedCreatorClassSuffixList: [''],
            ),
        );
    }

    /**
     * @throws Throwable
     */
    public function testIsAllowedCreatorClassNameWithGlobalClassNameSupportsSuffixMatchingSucceeds(): void
    {
        self::assertTrue(
            $this->containingClassResolver->isAllowedCreatorClassName(
                className: 'GlobalBuilder',
                allowedCreatorClassSuffixList: ['Builder'],
            ),
        );
    }
}

