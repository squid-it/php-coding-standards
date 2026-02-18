<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use SquidIT\PhpCodingStandards\PHPStan\Support\ContainingClassResolver;
use SquidIT\PhpCodingStandards\PHPStan\Support\VoDtoClassifier;

/**
 * Disallows service instantiation in non-creator classes.
 *
 * This rule allows `new` only when:
 * - The containing class name ends with an allowed creator suffix, or
 * - The instantiated class is internal/builtin, or
 * - The instantiated class is classified as VO/DTO by `VoDtoClassifier`.
 *
 * Default allowed creator suffixes:
 * - `Factory`
 * - `Builder`
 * - `Provider`
 *
 * Valid examples:
 * - `new DateTimeImmutable()` inside any class.
 * - `new HttpClient()` inside `HttpClientFactory`.
 * - `new OrderDto(...)` when the class passes VO/DTO gates.
 *
 * Invalid example:
 * - `new HttpClient()` inside `ReportService`.
 *
 * @implements Rule<New_>
 */
final readonly class NoServiceInstantiationRule implements Rule
{
    /** @var array<int, string> */
    private array $allowedCreatorClassSuffixList;

    /**
     * @param array<int, string> $allowedCreatorClassSuffixList
     */
    public function __construct(
        array $allowedCreatorClassSuffixList = ContainingClassResolver::DEFAULT_ALLOWED_CREATOR_CLASS_SUFFIX_LIST,
        private VoDtoClassifier $voDtoClassifier = new VoDtoClassifier(),
        private ContainingClassResolver $containingClassResolver = new ContainingClassResolver(),
    ) {
        $this->allowedCreatorClassSuffixList = $this->normalizeAllowedCreatorClassSuffixList(
            $allowedCreatorClassSuffixList,
        );
    }

    public function getNodeType(): string
    {
        return New_::class;
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (($node instanceof New_) === false) {
            return [];
        }

        if ($node->class instanceof Node\Stmt\Class_) {
            return [];
        }

        $containingClassName = $this->containingClassResolver->resolveContainingClassName($scope);

        if ($containingClassName === null) {
            return [];
        }

        if (
            $this->containingClassResolver->isAllowedCreatorClassName(
                $containingClassName,
                $this->allowedCreatorClassSuffixList,
            ) === true
        ) {
            return [];
        }

        $instantiatedClassReflectionList = $this->resolveInstantiatedClassReflectionList($scope, $node);

        if (count($instantiatedClassReflectionList) === 0) {
            return [];
        }

        $errorList = [];

        foreach ($instantiatedClassReflectionList as $instantiatedClassReflection) {
            if ($this->voDtoClassifier->isVoDtoClass($instantiatedClassReflection) === true) {
                continue;
            }

            $errorList[] = RuleErrorBuilder::message(
                $this->buildNoServiceInstantiationMessage(
                    $instantiatedClassReflection->getName(),
                    $containingClassName,
                ),
            )
                ->identifier('squidit.architecture.noServiceInstantiation')
                ->line($node->getStartLine())
                ->build();
        }

        return $errorList;
    }

    /**
     * @return array<int, ClassReflection>
     */
    private function resolveInstantiatedClassReflectionList(Scope $scope, New_ $newNode): array
    {
        $instantiatedClassReflectionList = [];
        $newType                         = $scope->getType($newNode);

        foreach ($newType->getObjectClassReflections() as $instantiatedClassReflection) {
            $this->addUniqueClassReflection($instantiatedClassReflectionList, $instantiatedClassReflection);
        }

        return $instantiatedClassReflectionList;
    }

    /**
     * @param array<int, ClassReflection> $classReflectionList
     */
    private function addUniqueClassReflection(array &$classReflectionList, ClassReflection $classReflection): void
    {
        foreach ($classReflectionList as $existingClassReflection) {
            if ($existingClassReflection->getName() === $classReflection->getName()) {
                return;
            }
        }

        $classReflectionList[] = $classReflection;
    }

    private function buildNoServiceInstantiationMessage(string $instantiatedClassName, string $containingClassName): string
    {
        return sprintf(
            'Instantiation of service "%s" is not allowed in non-creator class "%s". Move creation to a class ending with %s or inject the dependency.',
            $this->extractShortClassName($instantiatedClassName),
            $this->extractShortClassName($containingClassName),
            $this->buildAllowedCreatorSuffixListText(),
        );
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
     * @param array<int, string> $allowedCreatorClassSuffixList
     *
     * @return array<int, string>
     */
    private function normalizeAllowedCreatorClassSuffixList(array $allowedCreatorClassSuffixList): array
    {
        $normalizedAllowedCreatorClassSuffixList = [];

        foreach ($allowedCreatorClassSuffixList as $allowedCreatorClassSuffix) {
            $trimmedAllowedCreatorClassSuffix = trim($allowedCreatorClassSuffix);

            if ($trimmedAllowedCreatorClassSuffix === '') {
                continue;
            }

            if (
                $this->isCreatorClassSuffixInList(
                    $normalizedAllowedCreatorClassSuffixList,
                    $trimmedAllowedCreatorClassSuffix,
                ) === true
            ) {
                continue;
            }

            $normalizedAllowedCreatorClassSuffixList[] = $trimmedAllowedCreatorClassSuffix;
        }

        if (count($normalizedAllowedCreatorClassSuffixList) === 0) {
            return ContainingClassResolver::DEFAULT_ALLOWED_CREATOR_CLASS_SUFFIX_LIST;
        }

        return $normalizedAllowedCreatorClassSuffixList;
    }

    /**
     * @param array<int, string> $allowedCreatorClassSuffixList
     */
    private function isCreatorClassSuffixInList(array $allowedCreatorClassSuffixList, string $searchSuffix): bool
    {
        return in_array($searchSuffix, $allowedCreatorClassSuffixList, true) === true;
    }

    private function buildAllowedCreatorSuffixListText(): string
    {
        $allowedCreatorClassPatternList = [];

        foreach ($this->allowedCreatorClassSuffixList as $allowedCreatorClassSuffix) {
            $allowedCreatorClassPatternList[] = sprintf('"%s%s"', '*', $allowedCreatorClassSuffix);
        }

        return $this->buildHumanReadableList($allowedCreatorClassPatternList);
    }

    /**
     * @param array<int, string> $valueList
     */
    private function buildHumanReadableList(array $valueList): string
    {
        if (count($valueList) === 0) {
            return '"*Factory"';
        }

        if (count($valueList) === 1) {
            return $valueList[0];
        }

        $humanReadableList = '';
        $lastListIndex     = count($valueList) - 1;

        foreach ($valueList as $index => $value) {
            if ($index === 0) {
                $humanReadableList = $value;

                continue;
            }

            if ($index === $lastListIndex) {
                $humanReadableList .= sprintf(', or %s', $value);

                continue;
            }

            $humanReadableList .= sprintf(', %s', $value);
        }

        return $humanReadableList;
    }
}
