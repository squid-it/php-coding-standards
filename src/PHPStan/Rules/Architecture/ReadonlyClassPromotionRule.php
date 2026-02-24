<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Suggests class-level readonly promotion when every declared property is readonly.
 *
 * Safety gates:
 * - Applies only to final classes to avoid inheritance breakage.
 * - Skips classes with a parent class (`extends`).
 *
 * @implements Rule<Class_>
 */
final class ReadonlyClassPromotionRule implements Rule
{
    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (($node instanceof Class_) === false) {
            return [];
        }

        if ($this->shouldReportClass($node) === false) {
            return [];
        }

        if ($node->name === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Final class "%s" can be declared readonly because all declared properties are readonly. Convert it to a readonly class and remove property-level readonly modifiers.',
                    $node->name->toString(),
                ),
            )
                ->identifier('squidit.architecture.readonlyClassPromotion')
                ->line($node->getStartLine())
                ->build(),
        ];
    }

    private function shouldReportClass(Class_ $classNode): bool
    {
        if ($classNode->name === null) {
            return false;
        }

        if ($classNode->isReadonly() === true) {
            return false;
        }

        if ($classNode->isFinal() === false) {
            return false;
        }

        if ($classNode->extends !== null) {
            return false;
        }

        $declaredPropertyCount = 0;

        foreach ($classNode->getProperties() as $propertyNode) {
            $declaredPropertyCount += count($propertyNode->props);

            if ($propertyNode->isReadonly() === false) {
                return false;
            }
        }

        foreach ($this->resolvePromotedPropertyParameterList($classNode) as $parameterNode) {
            $declaredPropertyCount++;

            if ($parameterNode->isReadonly() === false) {
                return false;
            }
        }

        return $declaredPropertyCount > 0;
    }

    /**
     * @return array<int, Param>
     */
    private function resolvePromotedPropertyParameterList(Class_ $classNode): array
    {
        $promotedPropertyParameterList = [];

        foreach ($classNode->getMethods() as $methodNode) {
            if (strtolower($methodNode->name->toString()) !== '__construct') {
                continue;
            }

            foreach ($methodNode->params as $parameterNode) {
                if ($parameterNode->isPromoted() === false) {
                    continue;
                }

                $promotedPropertyParameterList[] = $parameterNode;
            }

            break;
        }

        return $promotedPropertyParameterList;
    }
}
