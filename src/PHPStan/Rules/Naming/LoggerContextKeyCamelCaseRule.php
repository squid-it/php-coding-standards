<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Rules\Naming;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;
use Psr\Log\LoggerInterface;

/**
 * Enforces camelCase string-literal keys in logger context arrays.
 *
 * This rule applies only when:
 * - The receiver type is compatible with `Psr\Log\LoggerInterface`.
 * - The context argument is present and is an inline array literal.
 * - The key is a string literal.
 *
 * Valid examples:
 * - `$logger->info('Saved', ['fooBar' => 1]);`
 * - `$logger->log('info', 'Saved', ['userId' => 1]);`
 *
 * Invalid example:
 * - `$logger->info('Saved', ['foo_bar' => 1]);`
 *
 * @implements Rule<MethodCall>
 */
final readonly class LoggerContextKeyCamelCaseRule implements Rule
{
    /** @var array<string, int> */
    private const array LOGGER_METHOD_CONTEXT_POSITION_MAP = [
        'emergency' => 1,
        'alert'     => 1,
        'critical'  => 1,
        'error'     => 1,
        'warning'   => 1,
        'notice'    => 1,
        'info'      => 1,
        'debug'     => 1,
        'log'       => 2,
    ];

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (($node instanceof MethodCall) === false) {
            return [];
        }

        if (($node->name instanceof Identifier) === false) {
            return [];
        }

        if ($this->isLoggerReceiver($scope, $node) === false) {
            return [];
        }

        $contextArgumentPosition = $this->resolveContextArgumentPosition($node->name);

        if ($contextArgumentPosition === null) {
            return [];
        }

        if (array_key_exists($contextArgumentPosition, $node->args) === false) {
            return [];
        }

        $contextArgumentNode = $node->args[$contextArgumentPosition];

        if (($contextArgumentNode instanceof Arg) === false) {
            return [];
        }

        $contextArgumentValue = $contextArgumentNode->value;

        if (($contextArgumentValue instanceof Array_) === false) {
            return [];
        }

        return $this->buildRuleErrorListFromContextArray($node, $contextArgumentValue);
    }

    private function isLoggerReceiver(Scope $scope, MethodCall $methodCallNode): bool
    {
        $loggerReceiverType = $scope->getType($methodCallNode->var);
        $loggerType         = new ObjectType(LoggerInterface::class);

        return $loggerType->isSuperTypeOf($loggerReceiverType)->yes();
    }

    private function resolveContextArgumentPosition(Identifier $methodNameIdentifier): ?int
    {
        $methodName = strtolower($methodNameIdentifier->toString());

        if (array_key_exists($methodName, self::LOGGER_METHOD_CONTEXT_POSITION_MAP) === false) {
            return null;
        }

        return self::LOGGER_METHOD_CONTEXT_POSITION_MAP[$methodName];
    }

    /**
     * @throws ShouldNotHappenException
     *
     * @return array<int, RuleError>
     */
    private function buildRuleErrorListFromContextArray(MethodCall $methodCallNode, Array_ $contextArrayNode): array
    {
        $errorList = [];

        foreach ($contextArrayNode->items as $arrayItemNode) {
            if ($arrayItemNode === null) {
                continue;
            }

            if (($arrayItemNode->key instanceof String_) === false) {
                continue;
            }

            $contextKey = $arrayItemNode->key->value;

            if ($this->isCamelCase($contextKey) === true) {
                continue;
            }

            $errorList[] = RuleErrorBuilder::message(
                $this->buildContextKeyCamelCaseMessage($contextKey, $methodCallNode),
            )
                ->identifier('squidit.naming.loggerContextKeyCamelCase')
                ->line($arrayItemNode->key->getStartLine())
                ->build();
        }

        return $errorList;
    }

    private function isCamelCase(string $value): bool
    {
        return (bool) preg_match('/^[a-z][a-zA-Z0-9]*$/', $value);
    }

    private function buildContextKeyCamelCaseMessage(string $contextKey, MethodCall $methodCallNode): string
    {
        $methodName = $methodCallNode->name instanceof Identifier
            ? $methodCallNode->name->toString()
            : 'loggerMethod';

        return sprintf(
            'Logger context key "%s" in %s() must be camelCase.',
            $contextKey,
            $methodName,
        );
    }
}
