<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use ReflectionClass;

final class TypeCandidateResolver
{
    /** @var array<string, array<int, string>> */
    private array $allowedBaseNameListByFqcn = [];

    public function __construct(
        private readonly NameNormalizer $nameNormalizer = new NameNormalizer(),
        private readonly DenyList $denyList = new DenyList(),
    ) {}

    /**
     * Resolve variable-name candidates from a PHPStan inferred type.
     *
     * Behavior:
     * - Processes named object types only.
     * - Ignores non-object union members (for example: null/false).
     * - Expands class hierarchy (self, parents, interfaces).
     * - Excludes internal/builtin symbols based on ReflectionClass::isInternal().
     * - Applies deny-list filtering for FQCNs and candidate names.
     *
     * @return array<int, string>
     */
    public function resolvePHPStanType(Type $type): array
    {
        $candidateNameList = [];
        $classNameList     = $this->extractNamedObjectClassNameList($type);

        foreach ($classNameList as $className) {
            if ($this->denyList->isClassNameDenied($className) === true) {
                continue;
            }

            $resolvedCandidateNameList = $this->resolveCandidateNameListForClassName($className);

            foreach ($resolvedCandidateNameList as $candidateName) {
                $this->addUniqueString($candidateNameList, $candidateName);
            }
        }

        return $candidateNameList;
    }

    /**
     * Resolve interface-derived base names from a PHPStan type for interface bare-name checks.
     *
     * This resolver only considers directly-typed interfaces from the provided type.
     * Implemented/parent interface expansion from concrete or abstract classes is intentionally ignored.
     *
     * @return array<string, string> key: normalized base name, value: short interface type name
     */
    public function resolveInterfaceBaseNameToTypeMap(Type $type): array
    {
        $interfaceBaseNameToTypeMap = [];
        $classNameList              = $this->extractNamedObjectClassNameList($type);

        foreach ($classNameList as $className) {
            if ($this->denyList->isClassNameDenied($className) === true) {
                continue;
            }

            $isInterface  = false;
            $resolvedName = $className;

            $classReflection = $this->resolveClassReflection($className);

            if ($classReflection !== null) {
                if ($classReflection->isBuiltin() === true) {
                    continue;
                }

                $isInterface  = $classReflection->isInterface();
                $resolvedName = $classReflection->getName();
            } else {
                $nativeReflection = $this->reflectClassNative($className);

                if ($nativeReflection === null || $nativeReflection->isInternal() === true) {
                    continue;
                }

                $isInterface  = $nativeReflection->isInterface();
                $resolvedName = $nativeReflection->getName();
            }

            if ($isInterface === false) {
                continue;
            }

            $normalizedBaseNameList = $this->nameNormalizer->normalize($resolvedName);
            $shortInterfaceName     = $this->extractShortClassName($resolvedName);

            foreach ($normalizedBaseNameList as $normalizedBaseName) {
                if ($this->denyList->isCandidateNameDenied($normalizedBaseName) === true) {
                    continue;
                }

                if (array_key_exists($normalizedBaseName, $interfaceBaseNameToTypeMap) === true) {
                    continue;
                }

                $interfaceBaseNameToTypeMap[$normalizedBaseName] = $shortInterfaceName;
            }
        }

        return $interfaceBaseNameToTypeMap;
    }

    /**
     * @return array<int, string>
     */
    private function resolveCandidateNameListForClassName(string $className): array
    {
        if (array_key_exists($className, $this->allowedBaseNameListByFqcn) === true) {
            return $this->allowedBaseNameListByFqcn[$className];
        }

        $candidateNameList      = [];
        $hierarchyClassNameList = $this->expandHierarchyClassNameList($className);

        foreach ($hierarchyClassNameList as $hierarchyClassName) {
            if ($this->denyList->isClassNameDenied($hierarchyClassName) === true) {
                continue;
            }

            $normalizedNameList = $this->nameNormalizer->normalize($hierarchyClassName);

            foreach ($normalizedNameList as $normalizedName) {
                if ($this->denyList->isCandidateNameDenied($normalizedName) === true) {
                    continue;
                }

                $this->addUniqueString($candidateNameList, $normalizedName);
            }
        }

        $this->allowedBaseNameListByFqcn[$className] = $candidateNameList;

        return $candidateNameList;
    }

    /**
     * @return array<int, string>
     */
    private function expandHierarchyClassNameList(string $className): array
    {
        $classReflection = $this->resolveClassReflection($className);

        if ($classReflection !== null) {
            return $this->expandHierarchyViaClassReflection($classReflection);
        }

        return $this->expandHierarchyNative($className);
    }

    /**
     * Resolve ClassReflection via PHPStan's type system.
     *
     * ObjectType::getClassReflection() uses PHPStan's ReflectionProviderStaticAccessor — the
     * analysis-time reflection provider that knows about every file in the analysis (including
     * scanFiles). This is preferred over native PHP reflection because it works for classes that
     * are not Composer-autoloaded (for example, classes in isolated fixture namespaces).
     */
    private function resolveClassReflection(string $className): ?ClassReflection
    {
        return (new ObjectType($className))->getClassReflection();
    }

    /**
     * @return array<int, string>
     */
    private function expandHierarchyViaClassReflection(ClassReflection $classReflection): array
    {
        if ($classReflection->isBuiltin() === true) {
            return [];
        }

        $hierarchyClassNameList = [];
        $this->addUniqueString($hierarchyClassNameList, $classReflection->getName());

        $parentClass = $classReflection->getParentClass();

        while ($parentClass instanceof ClassReflection) {
            if ($parentClass->isBuiltin() === false) {
                $this->addUniqueString($hierarchyClassNameList, $parentClass->getName());
            }

            $parentClass = $parentClass->getParentClass();
        }

        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            if (($interfaceReflection instanceof ClassReflection) === false) {
                continue;
            }

            if ($interfaceReflection->isBuiltin() === true) {
                continue;
            }

            $this->addUniqueString($hierarchyClassNameList, $interfaceReflection->getName());
        }

        return $hierarchyClassNameList;
    }

    /**
     * @return array<int, string>
     */
    private function expandHierarchyNative(string $className): array
    {
        $reflectionClass = $this->reflectClassNative($className);

        if ($reflectionClass === null) {
            return [$className];
        }

        if ($reflectionClass->isInternal() === true) {
            return [];
        }

        $hierarchyClassNameList = [];
        $this->addUniqueString($hierarchyClassNameList, $reflectionClass->getName());

        $parentClass = $reflectionClass->getParentClass();

        while ($parentClass !== false) {
            if ($parentClass->isInternal() === false) {
                $this->addUniqueString($hierarchyClassNameList, $parentClass->getName());
            }

            $parentClass = $parentClass->getParentClass();
        }

        $interfaceReflectionList = $reflectionClass->getInterfaces();

        foreach ($interfaceReflectionList as $interfaceReflection) {
            if ($interfaceReflection->isInternal() === true) {
                continue;
            }

            $this->addUniqueString($hierarchyClassNameList, $interfaceReflection->getName());
        }

        return $hierarchyClassNameList;
    }

    /**
     * @return array<int, string>
     */
    private function extractNamedObjectClassNameList(Type $type): array
    {
        $classNameList = [];

        $flattenedTypeList = TypeUtils::flattenTypes($type);

        foreach ($flattenedTypeList as $flattenedType) {
            foreach ($flattenedType->getObjectClassNames() as $className) {
                $this->addUniqueString($classNameList, $className);
            }
        }

        return $classNameList;
    }

    private function extractShortClassName(string $className): string
    {
        $lastNamespaceSeparatorPosition = strrpos($className, '\\');

        if ($lastNamespaceSeparatorPosition === false) {
            return $className;
        }

        return substr($className, $lastNamespaceSeparatorPosition + 1);
    }

    /**
     * @return ReflectionClass<object>|null
     */
    private function reflectClassNative(string $className): ?ReflectionClass
    {
        if (class_exists($className) === false && interface_exists($className) === false) {
            return null;
        }

        /** @var class-string<object> $classLikeName */
        $classLikeName = $className;

        return new ReflectionClass($classLikeName);
    }

    /**
     * @param array<int, string> $stringList
     */
    private function addUniqueString(array &$stringList, string $value): void
    {
        if (in_array($value, $stringList, true) === false) {
            $stringList[] = $value;
        }
    }
}
