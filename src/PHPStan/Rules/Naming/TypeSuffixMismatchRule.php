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
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType as ParserUnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use SquidIT\PhpCodingStandards\PHPStan\Support\PhpDocTypeResolver;
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
 *   - Inline `@var` narrowing on the assignment statement is respected.
 * - Typed properties: `private FooData $service;` (reported)
 *   - Property-level `@var` narrowing is respected.
 * - Promoted properties: `public function __construct(private FooData $service)` (reported)
 *   - Parameter-level `@var` narrowing and constructor `@param` narrowing are respected.
 *
 * Valid examples:
 * - `private FooData $fooData;`
 * - `private FooData $initialFooData;`
 * - `$localFooData = new FooData();`
 *
 * Optional interface bare-name check (disabled by default):
 * - `private ChannelInterface $channel;` reports `squidit.naming.interfaceBareName`
 *   when enabled, to encourage contextual names like `$readChannel`.
 *
 * @implements Rule<Node>
 */
final readonly class TypeSuffixMismatchRule implements Rule
{
    private bool $isCoverageModeEnabled;

    public function __construct(
        private TypeCandidateResolver $typeCandidateResolver = new TypeCandidateResolver(),
        private VariableNameMatcher $variableNameMatcher = new VariableNameMatcher(),
        private TypeMessageDescriber $typeMessageDescriber = new TypeMessageDescriber(),
        private PhpDocTypeResolver $phpDocTypeResolver = new PhpDocTypeResolver(),
        private bool $enableInterfaceBareNameCheck = false,
    ) {
        $xdebugMode = getenv('XDEBUG_MODE');

        $this->isCoverageModeEnabled = $xdebugMode !== false && str_contains((string) $xdebugMode, 'coverage');
    }

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
        if ($node instanceof Expression && $node->expr instanceof Assign) {
            return $this->processAssignmentNode(
                node: $node->expr,
                scope: $scope,
                statementDocCommentText: $node->getDocComment()?->getText(),
            );
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
    private function processAssignmentNode(
        Assign $node,
        Scope $scope,
        ?string $statementDocCommentText = null,
    ): array {
        if (($node->var instanceof Variable) === false) {
            return [];
        }

        if (is_string($node->var->name) === false) {
            return [];
        }

        $variableName = $node->var->name;
        $type         = $this->resolveAssignmentType(
            node: $node,
            variableName: $variableName,
            scope: $scope,
            statementDocCommentText: $statementDocCommentText,
        );

        return $this->buildRuleErrorList($variableName, $type, $node->getStartLine());
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    private function processTypedPropertyNode(Property $node, Scope $scope): array
    {
        $errorList = [];

        foreach ($node->props as $propertyPropertyNode) {
            $propertyName = $propertyPropertyNode->name->toString();
            $type         = $this->resolveTypedPropertyType($node, $propertyName, $scope);

            if ($type === null) {
                continue;
            }

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

        $propertyName = $node->var->name;
        $type         = $this->resolvePromotedPropertyType($node, $scope);

        if ($type === null) {
            return [];
        }

        $ruleErrorList = $this->buildRuleErrorList($propertyName, $type, $node->getStartLine());

        if (count($ruleErrorList) > 0) {
            // When a @param or @var docblock narrowed the type, docblock-derived class names are
            // short (unqualified) strings that scope cannot resolve to FQCNs — scope.resolveName()
            // is a pass-through that only handles self/static/parent. This means hierarchy
            // expansion for docblock types may be incomplete (e.g. a sub-interface whose parent
            // lives only in scanFiles). Allow the declared property type as an additional check so
            // that parent-interface base names remain valid candidates.
            $declaredType = $this->resolveTypeFromTypeNode($node->type, $scope);

            if ($declaredType !== null) {
                $declaredCandidateList = $this->typeCandidateResolver->resolvePHPStanType($declaredType);

                if ($this->isValidForAnyCandidateBaseName($propertyName, $declaredCandidateList)) {
                    return [];
                }
            }
        }

        return $ruleErrorList;
    }

    private function resolveAssignmentType(
        Assign $node,
        string $variableName,
        Scope $scope,
        ?string $statementDocCommentText,
    ): Type {
        $docCommentType = $this->resolveVarTagTypeFromDocCommentText(
            docCommentText: $statementDocCommentText,
            variableName: $variableName,
            allowUnnamedVarTag: true,
        );

        if ($docCommentType !== null) {
            return $docCommentType;
        }

        $docCommentType = $this->resolveVarTagTypeFromNode(
            node: $node,
            variableName: $variableName,
            allowUnnamedVarTag: true,
        );

        if ($docCommentType !== null) {
            return $docCommentType;
        }

        return $scope->getType($node->expr);
    }

    /**
     * @throws ShouldNotHappenException
     */
    private function resolvePromotedPropertyType(Param $node, Scope $scope): ?Type
    {
        if (($node->var instanceof Variable) === false) {
            return null;
        }

        if (is_string($node->var->name) === false) {
            return $this->resolveTypeFromTypeNode($node->type, $scope);
        }

        $docCommentType = $this->resolveVarTagTypeFromNode(
            node: $node,
            variableName: $node->var->name,
            allowUnnamedVarTag: true,
        );

        if ($docCommentType !== null) {
            return $docCommentType;
        }

        // In coverage mode this branch can trigger a Windows/Xdebug crash in RuleTestCase runs.
        if ($this->isCoverageModeEnabled() === false) {
            $docCommentType = $this->resolveParamTagTypeFromPromotedPropertyNode($node, $scope);

            if ($docCommentType !== null) {
                return $docCommentType;
            }
        }

        return $this->resolveTypeFromTypeNode($node->type, $scope);
    }

    /**
     * @throws ShouldNotHappenException
     */
    private function resolveTypedPropertyType(Property $node, string $propertyName, Scope $scope): ?Type
    {
        $docCommentType = $this->resolveVarTagTypeFromNode(
            node: $node,
            variableName: $propertyName,
            allowUnnamedVarTag: count($node->props) === 1,
        );

        return $docCommentType ?? $this->resolveTypeFromTypeNode($node->type, $scope);
    }

    private function resolveVarTagTypeFromNode(
        Node $node,
        string $variableName,
        bool $allowUnnamedVarTag,
    ): ?Type {
        $docCommentText = $this->resolveDocCommentText($node);

        if ($docCommentText === null) {
            return null;
        }

        return $this->resolveVarTagTypeFromDocCommentText(
            docCommentText: $docCommentText,
            variableName: $variableName,
            allowUnnamedVarTag: $allowUnnamedVarTag,
        );
    }

    private function resolveVarTagTypeFromDocCommentText(
        ?string $docCommentText,
        string $variableName,
        bool $allowUnnamedVarTag,
    ): ?Type {
        return $this->phpDocTypeResolver->resolveVarTagObjectType(
            docCommentText: $docCommentText,
            variableName: $variableName,
            allowUnnamedVarTag: $allowUnnamedVarTag,
        );
    }

    private function resolveDocCommentText(Node $node): ?string
    {
        $docComment = $node->getDocComment();

        if ($docComment !== null) {
            return $docComment->getText();
        }

        return null;
    }

    private function resolveParamTagTypeFromPromotedPropertyNode(Param $node, Scope $scope): ?Type
    {
        if (($node->var instanceof Variable) === false) {
            return null;
        }

        if (is_string($node->var->name) === false) {
            return null;
        }

        $docCommentText = $this->resolveConstructorDocCommentTextForPromotedParameter($node, $scope);

        if ($docCommentText === null) {
            return null;
        }

        return $this->phpDocTypeResolver->resolveNamedTagObjectType(
            docCommentText: $docCommentText,
            tagName: 'param',
            variableName: $node->var->name,
        );
    }

    private function isCoverageModeEnabled(): bool
    {
        return $this->isCoverageModeEnabled;
    }

    private function resolveConstructorDocCommentTextForPromotedParameter(Param $node, Scope $scope): ?string
    {
        $filePath = $scope->getFile();
        $lineList = file($filePath, FILE_IGNORE_NEW_LINES);

        if ($lineList === false || count($lineList) === 0) {
            return null;
        }

        $maxLineIndex       = count($lineList)      - 1;
        $parameterLineIndex = $node->getStartLine() - 1;

        if ($parameterLineIndex < 0) {
            $parameterLineIndex = 0;
        }

        if ($parameterLineIndex > $maxLineIndex) {
            $parameterLineIndex = $maxLineIndex;
        }

        $constructorLineIndex = null;

        for ($lineIndex = $parameterLineIndex; $lineIndex >= 0; $lineIndex--) {
            $lineText = $lineList[$lineIndex];

            if (preg_match('/\bfunction\s+__construct\s*\(/', $lineText) === 1) {
                $constructorLineIndex = $lineIndex;

                break;
            }
        }

        if ($constructorLineIndex === null) {
            return null;
        }

        $docCommentEndLineIndex = $constructorLineIndex - 1;

        while ($docCommentEndLineIndex >= 0 && trim($lineList[$docCommentEndLineIndex]) === '') {
            $docCommentEndLineIndex--;
        }

        if ($docCommentEndLineIndex < 0) {
            return null;
        }

        if (str_contains($lineList[$docCommentEndLineIndex], '*/') === false) {
            return null;
        }

        $docCommentStartLineIndex = $docCommentEndLineIndex;

        while ($docCommentStartLineIndex >= 0) {
            if (str_contains($lineList[$docCommentStartLineIndex], '/**') === true) {
                break;
            }

            $docCommentStartLineIndex--;
        }

        if ($docCommentStartLineIndex < 0) {
            return null;
        }

        $docCommentLineList = array_slice(
            $lineList,
            $docCommentStartLineIndex,
            $docCommentEndLineIndex - $docCommentStartLineIndex + 1,
        );

        return implode("\n", $docCommentLineList);
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

        if ($this->enableInterfaceBareNameCheck === false) {
            return $errorList;
        }

        $interfaceBaseNameToTypeMap = $this->typeCandidateResolver->resolveInterfaceBaseNameToTypeMap($type);

        if (array_key_exists($name, $interfaceBaseNameToTypeMap) === true) {
            $errorList[] = RuleErrorBuilder::message(
                $this->buildInterfaceBareNameMessage(
                    $name,
                    $interfaceBaseNameToTypeMap[$name],
                ),
            )
                ->identifier('squidit.naming.interfaceBareName')
                ->line($line)
                ->build();
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

    private function buildInterfaceBareNameMessage(string $name, string $interfaceTypeName): string
    {
        return sprintf(
            'Interface-typed name "$%s" uses the bare interface base name "%s" (inferred interface type: %s). Prefer a contextual prefix like "$read%s".',
            $name,
            $name,
            $interfaceTypeName,
            ucfirst($name),
        );
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
}
