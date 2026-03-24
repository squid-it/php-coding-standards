<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Testing\PHPStanTestCase;
use SquidIT\PhpCodingStandards\PHPStan\Support\AllowedInvokableClassClassifier;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\AllowedInvokableClassClassifier\InheritedInvokableStatusReporter;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\AllowedInvokableClassClassifier\InheritedInvokableWithBehaviorMethod;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\AllowedInvokableClassClassifier\InvokableWithBehaviorMethod;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\AllowedInvokableClassClassifier\InvokableWithExactPrefixMethod;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\AllowedInvokableClassClassifier\InvokableWithLowercaseInspectorMethod;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\AllowedInvokableClassClassifier\MutableInvokableStatusReporter;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\AllowedInvokableClassClassifier\NonInvokableInspectorOnly;
use Throwable;

final class AllowedInvokableClassClassifierTest extends PHPStanTestCase
{
    private AllowedInvokableClassClassifier $allowedInvokableClassClassifier;
    private ReflectionProvider $reflectionProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->allowedInvokableClassClassifier = new AllowedInvokableClassClassifier();
        $this->reflectionProvider              = self::createReflectionProvider();
    }

    /**
     * @throws Throwable
     */
    public function testIsAllowedInvokableClassCachesClassificationByClassNameSucceeds(): void
    {
        $classReflection      = $this->reflectionProvider->getClass(MutableInvokableStatusReporter::class);
        $firstClassification  = $this->allowedInvokableClassClassifier->isAllowedInvokableClass($classReflection);
        $secondClassification = $this->allowedInvokableClassClassifier->isAllowedInvokableClass($classReflection);

        self::assertSame($firstClassification, $secondClassification);
    }

    /**
     * @throws Throwable
     */
    public function testIsAllowedInvokableClassWithInspectionApiSucceeds(): void
    {
        $classReflection = $this->reflectionProvider->getClass(MutableInvokableStatusReporter::class);

        self::assertTrue($this->allowedInvokableClassClassifier->isAllowedInvokableClass($classReflection));
    }

    /**
     * @throws Throwable
     */
    public function testIsAllowedInvokableClassWithInheritedInspectionApiSucceeds(): void
    {
        $classReflection = $this->reflectionProvider->getClass(InheritedInvokableStatusReporter::class);

        self::assertTrue($this->allowedInvokableClassClassifier->isAllowedInvokableClass($classReflection));
    }

    /**
     * @throws Throwable
     */
    public function testIsAllowedInvokableClassWithoutInvokeMethodReturnsFalseSucceeds(): void
    {
        $classReflection = $this->reflectionProvider->getClass(NonInvokableInspectorOnly::class);

        self::assertFalse($this->allowedInvokableClassClassifier->isAllowedInvokableClass($classReflection));
    }

    /**
     * @throws Throwable
     */
    public function testIsAllowedInvokableClassWithBehaviorMethodReturnsFalseSucceeds(): void
    {
        $classReflection = $this->reflectionProvider->getClass(InvokableWithBehaviorMethod::class);

        self::assertFalse($this->allowedInvokableClassClassifier->isAllowedInvokableClass($classReflection));
    }

    /**
     * @throws Throwable
     */
    public function testIsAllowedInvokableClassWithLowercaseInspectionMethodReturnsFalseSucceeds(): void
    {
        $classReflection = $this->reflectionProvider->getClass(InvokableWithLowercaseInspectorMethod::class);

        self::assertFalse($this->allowedInvokableClassClassifier->isAllowedInvokableClass($classReflection));
    }

    /**
     * @throws Throwable
     */
    public function testIsAllowedInvokableClassWithExactPrefixMethodReturnsFalseSucceeds(): void
    {
        $classReflection = $this->reflectionProvider->getClass(InvokableWithExactPrefixMethod::class);

        self::assertFalse($this->allowedInvokableClassClassifier->isAllowedInvokableClass($classReflection));
    }

    /**
     * @throws Throwable
     */
    public function testIsAllowedInvokableClassWithInheritedBehaviorMethodReturnsFalseSucceeds(): void
    {
        $classReflection = $this->reflectionProvider->getClass(InheritedInvokableWithBehaviorMethod::class);

        self::assertFalse($this->allowedInvokableClassClassifier->isAllowedInvokableClass($classReflection));
    }

    /**
     * @throws Throwable
     */
    public function testIsAllowedInvokableClassForInterfaceReturnsFalseSucceeds(): void
    {
        $classReflection = $this->reflectionProvider->getClass(Throwable::class);

        self::assertFalse($this->allowedInvokableClassClassifier->isAllowedInvokableClass($classReflection));
    }
}
