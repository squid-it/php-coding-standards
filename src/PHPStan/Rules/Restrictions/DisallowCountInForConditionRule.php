<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Rules\Restrictions;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\For_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<For_>
 */
final readonly class DisallowCountInForConditionRule implements Rule
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {}

    public function getNodeType(): string
    {
        return For_::class;
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $ruleErrorList   = [];
        $pendingNodeList = $node->cond;

        while (($conditionNode = array_pop($pendingNodeList)) !== null) {
            if ($conditionNode instanceof FunctionLike || $conditionNode instanceof ClassLike) {
                continue;
            }

            if ($conditionNode instanceof FuncCall
                && $conditionNode->isFirstClassCallable() === false
                && $conditionNode->name instanceof Name
                && strtolower($this->reflectionProvider->resolveFunctionName($conditionNode->name, $scope) ?? '') === 'count'
            ) {
                $ruleErrorList[] = RuleErrorBuilder::message(
                    'Calling count() in the second expression of a for loop is not allowed. Calculate the count in the first expression (the initializer) instead.',
                )
                    ->identifier('squidit.restrictions.disallowCountInForCondition')
                    ->line($conditionNode->getStartLine())
                    ->build();
            }

            foreach ($conditionNode->getSubNodeNames() as $subNodeName) {
                $subNode = $conditionNode->{$subNodeName};

                if ($subNode instanceof Node) {
                    $pendingNodeList[] = $subNode;
                } elseif (is_array($subNode)) {
                    foreach ($subNode as $childNode) {
                        if ($childNode instanceof Node) {
                            $pendingNodeList[] = $childNode;
                        }
                    }
                }
            }
        }

        return $ruleErrorList;
    }
}
