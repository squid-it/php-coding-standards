<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Rules\Restrictions;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<BooleanNot>
 */
final class DisallowLogicalNotRule implements Rule
{
    public function getNodeType(): string
    {
        return BooleanNot::class;
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        return [
            RuleErrorBuilder::message(
                'Using logical NOT (!) is not allowed. Use an explicit comparison instead (=== true, === false, !== null).',
            )
                ->identifier('squidit.restrictions.disallowLogicalNot')
                ->line($node->getStartLine())
                ->build(),
        ];
    }
}
