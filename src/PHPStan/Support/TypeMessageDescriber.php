<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;

final readonly class TypeMessageDescriber
{
    public function describeType(Type $type): string
    {
        $shortClassNameList = [];
        $flattenedTypeList  = TypeUtils::flattenTypes($type);

        foreach ($flattenedTypeList as $flattenedType) {
            foreach ($flattenedType->getObjectClassNames() as $className) {
                $shortClassName = $this->extractShortClassName($className);

                if (in_array($shortClassName, $shortClassNameList, true) === false) {
                    $shortClassNameList[] = $shortClassName;
                }
            }
        }

        if (count($shortClassNameList) === 0) {
            return $type->describe(VerbosityLevel::typeOnly());
        }

        sort($shortClassNameList);

        return implode('|', $shortClassNameList);
    }

    public function describeIterableValueType(Type $iterableType): string
    {
        if ($iterableType->isIterable()->yes() === false) {
            return $this->describeType($iterableType);
        }

        return $this->describeType($iterableType->getIterableValueType());
    }

    private function extractShortClassName(string $className): string
    {
        $lastNamespaceSeparatorPosition = strrpos($className, '\\');

        if ($lastNamespaceSeparatorPosition === false) {
            return $className;
        }

        return substr($className, $lastNamespaceSeparatorPosition + 1);
    }
}
