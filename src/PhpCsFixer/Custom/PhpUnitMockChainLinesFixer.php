<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PhpCsFixer\Custom;

use InvalidArgumentException;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;
use UnexpectedValueException;

/**
 * Makes each PHPUnit mock/stub configuration call start on its own line.
 *
 * This is deliberately conservative: unknown receiver types are handled only
 * when the chain contains ->expects(...). A ->method(...) chain is formatted
 * only if the receiver is a local created with createMock/createStub.
 */
final readonly class PhpUnitMockChainLinesFixer implements FixerInterface
{
    public const string NAME = 'SquidIT/php_unit_mock_chain_lines';

    public function __construct(
        private string $indent = '    ',
        private string $lineEnding = "\n",
    ) {}

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Put each PHPUnit mock/stub chain method call on a separate, indented line.',
            [new CodeSample(<<<'PHP'
                <?php
                $mock = self::createMock(Foo::class);
                $mock->expects(self::once())->method('foo')->willReturn(1);

                PHP)],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_OBJECT_OPERATOR);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function getPriority(): int
    {
        // Insert line breaks before the built-in indentation fixer runs.
        return 10;
    }

    public function supports(SplFileInfo $file): bool
    {
        return str_ends_with($file->getFilename(), 'Test.php');
    }

    /**
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        if ($this->supports($file) === false || $this->isCandidate($tokens) === false) {
            return;
        }

        $mockVariables             = [];
        $whitespaceByOperatorIndex = [];
        $lineContent               = '';

        foreach ($tokens as $tokenIndex => $token) {
            // Later chains on the same line must see the indentation of pending edits.
            if (isset($whitespaceByOperatorIndex[$tokenIndex]) === true) {
                $lineContent = substr($whitespaceByOperatorIndex[$tokenIndex], strlen($this->lineEnding));
            }

            $content      = $token->getContent();
            $newlineIndex = strrpos($content, "\n");
            $lineContent  = $newlineIndex === false ? $lineContent . $content : substr($content, $newlineIndex + 1);

            // Local variables in one test must not leak into another method.
            if ($token->isGivenKind(T_FUNCTION) === true) {
                $mockVariables = [];
            }

            if ($token->isGivenKind(T_VARIABLE) === false) {
                continue;
            }

            $variableName = $token->getContent();
            $nextIndex    = $tokens->getNextMeaningfulToken($tokenIndex);

            if ($nextIndex === null) {
                continue;
            }

            if ($tokens[$nextIndex]->equals('=') === true) {
                if ($this->isMockFactoryAssignment($tokens, $nextIndex) === true) {
                    $mockVariables[$variableName] = true;
                } else {
                    unset($mockVariables[$variableName]);
                }

                continue;
            }

            if ($tokens[$nextIndex]->isGivenKind(T_OBJECT_OPERATOR) === false) {
                continue;
            }

            // Walk only the outer chain, skipping entire argument lists. That
            // prevents modifying nested calls like ->with($other->foo()).
            [$operatorIndexes, $methodNames] = $this->getChainCalls($tokens, $tokenIndex);

            if ($operatorIndexes === []) {
                continue;
            }

            if (
                in_array('expects', $methodNames, true) === false
                && (isset($mockVariables[$variableName]) === false || in_array('method', $methodNames, true) === false)
            ) {
                continue;
            }

            $indent = substr($lineContent, 0, strspn($lineContent, " \t")) . $this->indent;

            foreach ($operatorIndexes as $operatorIndex) {
                // Don't move comments that a user intentionally placed before ->.
                $previousToken = $tokens[$operatorIndex - 1];

                if ($previousToken->isGivenKind([T_COMMENT, T_DOC_COMMENT]) === true) {
                    continue;
                }

                $whitespaceByOperatorIndex[$operatorIndex] = $this->lineEnding . $indent;
            }
        }

        // Edits in descending order preserve every original token index.
        krsort($whitespaceByOperatorIndex, SORT_NUMERIC);

        foreach ($whitespaceByOperatorIndex as $operatorIndex => $whitespace) {
            $tokens->ensureWhitespaceAtIndex($operatorIndex - 1, 1, $whitespace);
        }
    }

    private function isMockFactoryAssignment(Tokens $tokens, int $equalsIndex): bool
    {
        $receiverIndex = $tokens->getNextMeaningfulToken($equalsIndex);

        if ($receiverIndex === null) {
            return false;
        }

        $scopeIndex = $tokens->getNextMeaningfulToken($receiverIndex);

        if ($scopeIndex === null) {
            return false;
        }

        $isSelf = $tokens[$receiverIndex]->isGivenKind([T_STRING, T_STATIC]) === true
            && in_array(strtolower($tokens[$receiverIndex]->getContent()), ['self', 'static'], true) === true
            && $tokens[$scopeIndex]->isGivenKind(T_DOUBLE_COLON) === true;

        $isThis = $tokens[$receiverIndex]->isGivenKind(T_VARIABLE) === true
            && $tokens[$receiverIndex]->getContent() === '$this'
            && $tokens[$scopeIndex]->isGivenKind(T_OBJECT_OPERATOR) === true;

        if ($isSelf === false && $isThis === false) {
            return false;
        }

        $methodIndex = $tokens->getNextMeaningfulToken($scopeIndex);

        if (
            $methodIndex === null
            || $tokens[$methodIndex]->isGivenKind(T_STRING) === false
            || in_array($tokens[$methodIndex]->getContent(), ['createMock', 'createStub'], true) === false
        ) {
            return false;
        }

        $openParenthesisIndex = $tokens->getNextMeaningfulToken($methodIndex);

        return $openParenthesisIndex !== null && $tokens[$openParenthesisIndex]->equals('(') === true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     *
     * @return array{list<int>, list<string>}
     */
    private function getChainCalls(Tokens $tokens, int $variableIndex): array
    {
        $operatorIndexes = [];
        $methodNames     = [];
        $cursorIndex     = $variableIndex;

        while (true) {
            $operatorIndex = $tokens->getNextMeaningfulToken($cursorIndex);

            if ($operatorIndex === null || $tokens[$operatorIndex]->isGivenKind(T_OBJECT_OPERATOR) === false) {
                break;
            }

            $methodIndex = $tokens->getNextMeaningfulToken($operatorIndex);

            if ($methodIndex === null || $tokens[$methodIndex]->isGivenKind(T_STRING) === false) {
                break;
            }

            $openParenthesisIndex = $tokens->getNextMeaningfulToken($methodIndex);

            if ($openParenthesisIndex === null || $tokens[$openParenthesisIndex]->equals('(') === false) {
                break;
            }

            $operatorIndexes[] = $operatorIndex;
            $methodNames[]     = $tokens[$methodIndex]->getContent();
            $cursorIndex       = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openParenthesisIndex);
        }

        return [$operatorIndexes, $methodNames];
    }
}
