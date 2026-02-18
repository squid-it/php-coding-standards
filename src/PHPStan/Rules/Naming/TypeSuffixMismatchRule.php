<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Rules\Naming;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType as ParserIntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType as ParserUnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;
use SquidIT\PhpCodingStandards\PHPStan\Support\NameNormalizer;
use SquidIT\PhpCodingStandards\PHPStan\Support\TypeCandidateResolver;
use SquidIT\PhpCodingStandards\PHPStan\Support\TypeMessageDescriber;
use SquidIT\PhpCodingStandards\PHPStan\Support\VariableNameMatcher;

/**
 * Enforces type-aligned naming for object-typed variables and properties.
 *
 * This rule tries to prevent generic names (for example `$item` or `$service`) when the inferred
 * object type is known, because those names hide intent and make data flow harder to review.
 *
 * It checks:
 * - Assignments: `$item = new FooData();` (reported)
 * - Typed properties: `private FooData $service;` (reported)
 * - Promoted properties: `public function __construct(private FooData $service)` (reported)
 *
 * Valid examples:
 * - `private FooData $fooData;`
 * - `private FooData $initialFooData;`
 * - `$localFooData = new FooData();`
 *
 * Interface bare-name notice:
 * - `private ChannelInterface $channel;` reports `squidit.naming.interfaceBareNameNotice`
 *   to encourage contextual names like `$readChannel`.
 *
 * @implements Rule<Node>
 */
final readonly class TypeSuffixMismatchRule implements Rule
{
    public function __construct(
        private TypeCandidateResolver $typeCandidateResolver = new TypeCandidateResolver(),
        private VariableNameMatcher $variableNameMatcher = new VariableNameMatcher(),
        private NameNormalizer $nameNormalizer = new NameNormalizer(),
        private TypeMessageDescriber $typeMessageDescriber = new TypeMessageDescriber(),
    ) {}

    public function getNodeType(): string
    {
        return Node::class;
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof Assign) {
            return $this->processAssignmentNode($node, $scope);
        }

        if ($node instanceof Property) {
            return $this->processTypedPropertyNode($node, $scope);
        }

        if ($node instanceof Param) {
            return $this->processPromotedPropertyNode($node, $scope);
        }

        return [];
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    private function processAssignmentNode(Assign $node, Scope $scope): array
    {
        if (($node->var instanceof Variable) === false) {
            return [];
        }

        if (is_string($node->var->name) === false) {
            return [];
        }

        $variableName = $node->var->name;
        $type         = $scope->getType($node->expr);

        return $this->buildRuleErrorList($variableName, $type, $node->getStartLine());
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    private function processTypedPropertyNode(Property $node, Scope $scope): array
    {
        $type = $this->resolveTypeFromTypeNode($node->type, $scope);

        if ($type === null) {
            return [];
        }

        $errorList = [];

        foreach ($node->props as $propertyPropertyNode) {
            $propertyName  = $propertyPropertyNode->name->toString();
            $propertyLine  = $propertyPropertyNode->getStartLine();
            $ruleErrorList = $this->buildRuleErrorList($propertyName, $type, $propertyLine);

            foreach ($ruleErrorList as $ruleError) {
                $errorList[] = $ruleError;
            }
        }

        return $errorList;
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    private function processPromotedPropertyNode(Param $node, Scope $scope): array
    {
        if ($node->isPromoted() === false) {
            return [];
        }

        if (($node->var instanceof Variable) === false) {
            return [];
        }

        if (is_string($node->var->name) === false) {
            return [];
        }

        $type = $this->resolveTypeFromTypeNode($node->type, $scope);

        if ($type === null) {
            return [];
        }

        $propertyName = $node->var->name;

        return $this->buildRuleErrorList($propertyName, $type, $node->getStartLine());
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    private function buildRuleErrorList(string $name, Type $type, int $line): array
    {
        $candidateBaseNameList = $this->typeCandidateResolver->resolvePHPStanType($type);

        if (count($candidateBaseNameList) === 0) {
            return [];
        }

        sort($candidateBaseNameList);

        $errorList = [];

        if ($this->isValidForAnyCandidateBaseName($name, $candidateBaseNameList) === false) {
            $errorList[] = RuleErrorBuilder::message(
                $this->buildTypeSuffixMismatchMessage($name, $type, $candidateBaseNameList),
            )
                ->identifier('squidit.naming.typeSuffixMismatch')
                ->line($line)
                ->build();
        }

        $interfaceBaseNameToTypeMap = $this->resolveDirectInterfaceBaseNameToTypeMap($type);

        foreach ($interfaceBaseNameToTypeMap as $baseName => $interfaceTypeName) {
            if (
                $this->variableNameMatcher->shouldReportInterfaceBareNameNotice(
                    $name,
                    $baseName,
                ) === false
            ) {
                continue;
            }

            $errorList[] = RuleErrorBuilder::message(
                $this->buildInterfaceBareNameNoticeMessage($name, $baseName, $interfaceTypeName),
            )
                ->identifier('squidit.naming.interfaceBareNameNotice')
                ->line($line)
                ->build();

            break;
        }

        return $errorList;
    }

    /**
     * @param array<int, string> $candidateBaseNameList
     */
    private function isValidForAnyCandidateBaseName(string $name, array $candidateBaseNameList): bool
    {
        foreach ($candidateBaseNameList as $candidateBaseName) {
            if ($this->variableNameMatcher->isValid($name, $candidateBaseName) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, string> $candidateBaseNameList
     */
    private function buildTypeSuffixMismatchMessage(string $name, Type $type, array $candidateBaseNameList): string
    {
        $allowedBaseNameText = implode(', ', $candidateBaseNameList);
        $suffixNameList      = [];

        foreach ($candidateBaseNameList as $candidateBaseName) {
            $suffixNameList[] = ucfirst($candidateBaseName);
        }

        return sprintf(
            'Name "$%s" does not match inferred type "%s". Allowed base names: %s. Use one of these names directly or a contextual prefix ending with: %s.',
            $name,
            $this->typeMessageDescriber->describeType($type),
            $allowedBaseNameText,
            implode(', ', $suffixNameList),
        );
    }

    private function buildInterfaceBareNameNoticeMessage(string $name, string $baseName, string $interfaceTypeName): string
    {
        return sprintf(
            'Interface-typed name "$%s" uses the bare interface base name "%s" (inferred interface type: %s). Prefer a contextual prefix like "$read%s".',
            $name,
            $baseName,
            $interfaceTypeName,
            ucfirst($baseName),
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
     * @throws ShouldNotHappenException
     */
    private function resolveTypeFromTypeNode(ComplexType|Identifier|Name|null $typeNode, Scope $scope): ?Type
    {
        $objectClassNameList = $this->extractObjectClassNameListFromTypeNode($typeNode, $scope);

        if (count($objectClassNameList) === 0) {
            return null;
        }

        $objectTypeList = [];

        foreach ($objectClassNameList as $objectClassName) {
            $objectTypeList[] = new ObjectType($objectClassName);
        }

        if (count($objectTypeList) === 1) {
            return $objectTypeList[0];
        }

        return new UnionType($objectTypeList);
    }

    /**
     * @return array<int, string>
     */
    private function extractObjectClassNameListFromTypeNode(ComplexType|Identifier|Name|null $typeNode, Scope $scope): array
    {
        if ($typeNode === null) {
            return [];
        }

        if ($typeNode instanceof Name) {
            return [$scope->resolveName($typeNode)];
        }

        if ($typeNode instanceof Identifier) {
            return [];
        }

        if ($typeNode instanceof NullableType) {
            return $this->extractObjectClassNameListFromTypeNode($typeNode->type, $scope);
        }

        if (($typeNode instanceof ParserUnionType) === false && ($typeNode instanceof ParserIntersectionType) === false) {
            return [];
        }

        $objectClassNameList = [];

        foreach ($typeNode->types as $innerTypeNode) {
            $innerObjectClassNameList = $this->extractObjectClassNameListFromTypeNode($innerTypeNode, $scope);

            foreach ($innerObjectClassNameList as $innerObjectClassName) {
                if (in_array($innerObjectClassName, $objectClassNameList, true) === false) {
                    $objectClassNameList[] = $innerObjectClassName;
                }
            }
        }

        return $objectClassNameList;
    }

    /**
     * @return array<string, string> key: normalized base name, value: short interface type name
     */
    private function resolveDirectInterfaceBaseNameToTypeMap(Type $type): array
    {
        $interfaceBaseNameToTypeMap = [];
        $flattenedTypeList          = TypeUtils::flattenTypes($type);

        foreach ($flattenedTypeList as $flattenedType) {
            foreach ($flattenedType->getObjectClassNames() as $className) {
                $shortClassName = $this->extractShortClassName($className);

                if ($this->isInterfaceTypeName($shortClassName) === false) {
                    continue;
                }

                $normalizedBaseNameList = $this->nameNormalizer->normalize($className);

                foreach ($normalizedBaseNameList as $normalizedBaseName) {
                    if (array_key_exists($normalizedBaseName, $interfaceBaseNameToTypeMap) === true) {
                        continue;
                    }

                    $interfaceBaseNameToTypeMap[$normalizedBaseName] = $shortClassName;
                }
            }
        }

        return $interfaceBaseNameToTypeMap;
    }

    private function isInterfaceTypeName(string $shortClassName): bool
    {
        return str_ends_with($shortClassName, 'Interface');
    }
}
