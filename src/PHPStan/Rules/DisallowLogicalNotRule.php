<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

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
     * @return array<int, RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        return [
            RuleErrorBuilder::message(
                'Using logical NOT (!) is not allowed. Use an explicit comparison instead (=== true, === false, !== null).',
            )
                ->identifier('squidit.disallowLogicalNot')
                ->line($node->getStartLine())
                ->build(),
        ];
    }
}
