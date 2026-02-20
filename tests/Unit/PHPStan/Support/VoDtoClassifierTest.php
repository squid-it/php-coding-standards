<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Testing\PHPStanTestCase;
use SquidIT\PhpCodingStandards\PHPStan\Support\VoDtoClassifier;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\TypeCandidateResolver\CustomDomainDto;
use Throwable;

final class VoDtoClassifierTest extends PHPStanTestCase
{
    private VoDtoClassifier $voDtoClassifier;
    private ReflectionProvider $reflectionProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voDtoClassifier  = new VoDtoClassifier();
        $this->reflectionProvider = self::createReflectionProvider();
    }

    /**
     * @throws Throwable
     */
    public function testIsVoDtoClassCachesClassificationByClassNameSucceeds(): void
    {
        $classReflection         = $this->reflectionProvider->getClass(CustomDomainDto::class);
        $firstClassification     = $this->voDtoClassifier->isVoDtoClass($classReflection);
        $secondClassification    = $this->voDtoClassifier->isVoDtoClass($classReflection);

        self::assertSame($firstClassification, $secondClassification);
    }
}

