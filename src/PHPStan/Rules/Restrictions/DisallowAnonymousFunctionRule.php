<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Rules\Restrictions;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<FunctionLike>
 */
final class DisallowAnonymousFunctionRule implements Rule
{
    public function getNodeType(): string
    {
        return FunctionLike::class;
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof Closure) {
            return [
                RuleErrorBuilder::message(
                    'Anonymous functions (closures) are not allowed. Use an invokable class with an __invoke() method instead.',
                )
                    ->identifier('squidit.restrictions.disallowAnonymousFunction')
                    ->line($node->getStartLine())
                    ->build(),
            ];
        }

        if ($node instanceof ArrowFunction) {
            return [
                RuleErrorBuilder::message(
                    'Arrow functions are not allowed. Use an invokable class with an __invoke() method instead.',
                )
                    ->identifier('squidit.restrictions.disallowAnonymousFunction')
                    ->line($node->getStartLine())
                    ->build(),
            ];
        }

        return [];
    }
}
