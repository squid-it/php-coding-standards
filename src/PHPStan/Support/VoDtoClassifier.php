<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

use PHPStan\Reflection\ClassReflection;
use ReflectionMethod;

final class VoDtoClassifier
{
    /** @var array<string, bool> */
    private array $classificationByClassName = [];

    public function isVoDtoClass(ClassReflection $classReflection): bool
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
        if ($classReflection->isInternal() === true || $classReflection->isBuiltin() === true) {
            return true;
        }

        if ($classReflection->isClass() === false) {
            return false;
        }

        if ($this->passesImmutabilityGate($classReflection) === false) {
            return false;
        }

        return $this->passesPublicApiGate($classReflection);
    }

    private function passesImmutabilityGate(ClassReflection $classReflection): bool
    {
        if ($classReflection->isReadOnly() === true) {
            return true;
        }

        return $this->allDeclaredAndInheritedInstancePropertiesAreReadonly($classReflection);
    }

    private function allDeclaredAndInheritedInstancePropertiesAreReadonly(ClassReflection $classReflection): bool
    {
        $currentNativeReflection = $classReflection->getNativeReflection();

        while (true) {
            if ($currentNativeReflection->isInternal() === true) {
                break;
            }

            foreach ($currentNativeReflection->getProperties() as $nativePropertyReflection) {
                if (
                    $nativePropertyReflection->getDeclaringClass()->getName() !== $currentNativeReflection->getName()
                ) {
                    continue;
                }

                if ($nativePropertyReflection->isStatic() === true) {
                    continue;
                }

                if ($nativePropertyReflection->isReadOnly() === false) {
                    return false;
                }
            }

            $parentNativeReflection = $currentNativeReflection->getParentClass();

            if ($parentNativeReflection === false) {
                break;
            }

            $currentNativeReflection = $parentNativeReflection;
        }

        return true;
    }

    private function passesPublicApiGate(ClassReflection $classReflection): bool
    {
        $nativeReflection = $classReflection->getNativeReflection();

        foreach ($nativeReflection->getMethods(ReflectionMethod::IS_PUBLIC) as $nativeMethodReflection) {
            if ($nativeMethodReflection->getDeclaringClass()->getName() !== $classReflection->getName()) {
                continue;
            }

            if ($this->isAllowedPublicMethodName($nativeMethodReflection->getName()) === true) {
                continue;
            }

            return false;
        }

        return true;
    }

    private function isAllowedPublicMethodName(string $methodName): bool
    {
        if ($methodName === '__construct') {
            return true;
        }

        if ($this->hasCamelCaseBoundaryPrefix($methodName, 'get') === true) {
            return true;
        }

        if ($this->hasCamelCaseBoundaryPrefix($methodName, 'is') === true) {
            return true;
        }

        if ($this->hasCamelCaseBoundaryPrefix($methodName, 'has') === true) {
            return true;
        }

        return in_array($methodName, [
            'toArray',
            'jsonSerialize',
            '__toString',
            'equals',
            'equalsTo',
        ], true);
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
