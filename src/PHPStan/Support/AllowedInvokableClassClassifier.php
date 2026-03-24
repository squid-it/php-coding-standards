<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

use PHPStan\Reflection\ClassReflection;
use ReflectionMethod;

final class AllowedInvokableClassClassifier
{
    /** @var array<int, string> */
    private const array ALLOWED_INSPECTION_METHOD_PREFIX_LIST = [
        'get',
        'has',
        'is',
    ];

    /** @var array<string, bool> */
    private array $classificationByClassName = [];

    public function isAllowedInvokableClass(ClassReflection $classReflection): bool
    {
        $className = $classReflection->getName();

        if (array_key_exists($className, $this->classificationByClassName) === true) {
            return $this->classificationByClassName[$className];
        }

        $classification = $this->classify($classReflection);

        $this->classificationByClassName[$className] = $classification;

        return $classification;
    }

    private function classify(ClassReflection $classReflection): bool
    {
        if ($classReflection->isClass() === false) {
            return false;
        }

        if ($this->hasPublicInvokeMethod($classReflection) === false) {
            return false;
        }

        return $this->passesPublicApiGate($classReflection);
    }

    private function hasPublicInvokeMethod(ClassReflection $classReflection): bool
    {
        $nativeReflection = $classReflection->getNativeReflection();

        if ($nativeReflection->hasMethod('__invoke') === false) {
            return false;
        }

        return $nativeReflection->getMethod('__invoke')->isPublic();
    }

    private function passesPublicApiGate(ClassReflection $classReflection): bool
    {
        $nativeReflection = $classReflection->getNativeReflection();

        foreach ($nativeReflection->getMethods(ReflectionMethod::IS_PUBLIC) as $nativeMethodReflection) {
            if ($this->isAllowedPublicMethodName($nativeMethodReflection->getName()) === true) {
                continue;
            }

            return false;
        }

        return true;
    }

    private function isAllowedPublicMethodName(string $methodName): bool
    {
        if ($methodName === '__construct' || $methodName === '__invoke') {
            return true;
        }

        foreach (self::ALLOWED_INSPECTION_METHOD_PREFIX_LIST as $allowedMethodPrefix) {
            if ($this->hasCamelCaseBoundaryPrefix($methodName, $allowedMethodPrefix) === true) {
                return true;
            }
        }

        return false;
    }

    private function hasCamelCaseBoundaryPrefix(string $methodName, string $prefix): bool
    {
        if (str_starts_with($methodName, $prefix) === false) {
            return false;
        }

        $prefixLength = strlen($prefix);

        if (strlen($methodName) <= $prefixLength) {
            return false;
        }

        return ctype_upper($methodName[$prefixLength]);
    }
}
