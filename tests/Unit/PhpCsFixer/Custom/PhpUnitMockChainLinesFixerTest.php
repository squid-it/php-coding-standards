<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PhpCsFixer\Custom;

use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use SquidIT\PhpCodingStandards\PhpCsFixer\Custom\PhpUnitMockChainLinesFixer;
use Throwable;

final class PhpUnitMockChainLinesFixerTest extends TestCase
{
    private PhpUnitMockChainLinesFixer $phpUnitMockChainLinesFixer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phpUnitMockChainLinesFixer = new PhpUnitMockChainLinesFixer();
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function formattingCases(): array
    {
        return [
            'expectation with nested argument calls' => [
                <<<'PHP'
                    <?php
                        $mock->expects(self::once())->method('run')->with($other->first()->second())->willReturn(1);
                    PHP,
                <<<'PHP'
                    <?php
                        $mock
                            ->expects(self::once())
                            ->method('run')
                            ->with($other->first()->second())
                            ->willReturn(1);
                    PHP,
            ],
            'multiple chains preserve token indexes' => [
                <<<'PHP'
                    <?php
                    $first->expects(self::once())->method('one'); $second->expects(self::once())->method('two');
                    PHP,
                <<<'PHP'
                    <?php
                    $first
                        ->expects(self::once())
                        ->method('one'); $second
                            ->expects(self::once())
                            ->method('two');
                    PHP,
            ],
            'comments immediately before operators stay in place' => [
                <<<'PHP'
                    <?php
                    $mock/* receiver */->expects(self::once())/** method */->method('run')->willReturn(1);
                    PHP,
                <<<'PHP'
                    <?php
                    $mock/* receiver */->expects(self::once())/** method */->method('run')
                        ->willReturn(1);
                    PHP,
            ],
            'line comments remain on their original lines' => [
                <<<'PHP'
                    <?php
                        $mock // receiver
                            ->expects(self::once()) // expectation
                            ->method('run')->willReturn(1);
                    PHP,
                <<<'PHP'
                    <?php
                        $mock // receiver
                            ->expects(self::once()) // expectation
                            ->method('run')
                            ->willReturn(1);
                    PHP,
            ],
            'multiline arguments retain their contents' => [
                <<<'PHP'
                    <?php
                        $mock->expects(self::once())->method('run')->with(
                            $first,
                            $second,
                        )->willReturn(1);
                    PHP,
                <<<'PHP'
                    <?php
                        $mock
                            ->expects(self::once())
                            ->method('run')
                            ->with(
                            $first,
                            $second,
                        )
                            ->willReturn(1);
                    PHP,
            ],
            'reassignment clears tracked mock variables' => [
                <<<'PHP'
                    <?php
                    $mock = self::createMock(Foo::class);
                    $mock = $builder;
                    $mock->method('run')->willReturn(1);
                    PHP,
                <<<'PHP'
                    <?php
                    $mock = self::createMock(Foo::class);
                    $mock = $builder;
                    $mock->method('run')->willReturn(1);
                    PHP,
            ],
            'mock variables do not leak between methods' => [
                <<<'PHP'
                    <?php
                    function firstTest() {
                        $mock = self::createStub(Foo::class);
                    }
                    function secondTest() {
                        $mock->method('run')->willReturn(1);
                    }
                    PHP,
                <<<'PHP'
                    <?php
                    function firstTest() {
                        $mock = self::createStub(Foo::class);
                    }
                    function secondTest() {
                        $mock->method('run')->willReturn(1);
                    }
                    PHP,
            ],
            'unrelated fluent method chains remain unchanged' => [
                "<?php\n\$builder->method('run')->willReturn(1);",
                "<?php\n\$builder->method('run')->willReturn(1);",
            ],
            'unrelated factory receivers remain unchanged' => [
                "<?php\n\$mock = \$factory->createMock(Foo::class);\n\$mock->method('run')->willReturn(1);",
                "<?php\n\$mock = \$factory->createMock(Foo::class);\n\$mock->method('run')->willReturn(1);",
            ],
            'unrelated factory methods remain unchanged' => [
                "<?php\n\$mock = self::createBuilder(Foo::class);\n\$mock->method('run')->willReturn(1);",
                "<?php\n\$mock = self::createBuilder(Foo::class);\n\$mock->method('run')->willReturn(1);",
            ],
            'property access remains unchanged' => [
                "<?php\n\$builder->property->expects(self::once());",
                "<?php\n\$builder->property->expects(self::once());",
            ],
            'dynamic method calls remain unchanged' => [
                "<?php\n\$builder->{\$method}(self::once());",
                "<?php\n\$builder->{\$method}(self::once());",
            ],
            'no candidate tokens remain unchanged' => [
                "<?php\n\$value = 1;",
                "<?php\n\$value = 1;",
            ],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function factoryCalls(): array
    {
        return [
            'instance mock' => ['$this->createMock'],
            'instance stub' => ['$this->createStub'],
            'self mock'     => ['self::createMock'],
            'self stub'     => ['self::createStub'],
            'static mock'   => ['static::createMock'],
            'static stub'   => ['static::createStub'],
        ];
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('formattingCases')]
    public function testFormattingProducesExpectedCodeSucceeds(string $inputCode, string $expectedCode): void
    {
        $splFileInfo = new SplFileInfo('ExampleTest.php');
        $tokens      = Tokens::fromCode($inputCode);

        $this->phpUnitMockChainLinesFixer->fix($splFileInfo, $tokens);

        self::assertSame($expectedCode, $tokens->generateCode());

        $this->phpUnitMockChainLinesFixer->fix($splFileInfo, $tokens);

        self::assertSame($expectedCode, $tokens->generateCode());
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('factoryCalls')]
    public function testFactoryAssignmentEnablesMethodChainFormattingSucceeds(string $factoryCall): void
    {
        $assignment = '<?php $mock = ' . $factoryCall . '(Foo::class);' . "\n";
        $tokens     = Tokens::fromCode($assignment . "\$mock->method('run')->willReturn(1);");

        $this->phpUnitMockChainLinesFixer->fix(new SplFileInfo('ExampleTest.php'), $tokens);

        self::assertSame($assignment . "\$mock\n    ->method('run')\n    ->willReturn(1);", $tokens->generateCode());
    }

    /**
     * @throws Throwable
     */
    public function testCustomIndentationAndLineEndingsSucceeds(): void
    {
        $phpUnitMockChainLinesFixer = new PhpUnitMockChainLinesFixer("\t", "\r\n");
        $tokens                     = Tokens::fromCode("<?php\r\n\t\$mock->expects(self::once())->method('run');");

        $phpUnitMockChainLinesFixer->fix(new SplFileInfo('ExampleTest.php'), $tokens);

        self::assertSame("<?php\r\n\t\$mock\r\n\t\t->expects(self::once())\r\n\t\t->method('run');", $tokens->generateCode());
    }

    /**
     * @throws Throwable
     */
    public function testUnsupportedFileRemainsUnchangedSucceeds(): void
    {
        $inputCode = "<?php \$mock->expects(self::once())->method('run');";
        $tokens    = Tokens::fromCode($inputCode);

        $this->phpUnitMockChainLinesFixer->fix(new SplFileInfo('Example.php'), $tokens);

        self::assertSame($inputCode, $tokens->generateCode());
    }

    /**
     * @throws Throwable
     */
    public function testFixerMetadataSucceeds(): void
    {
        self::assertSame(PhpUnitMockChainLinesFixer::NAME, $this->phpUnitMockChainLinesFixer->getName());
        self::assertFalse($this->phpUnitMockChainLinesFixer->isRisky());
        self::assertSame(10, $this->phpUnitMockChainLinesFixer->getPriority());
        self::assertTrue($this->phpUnitMockChainLinesFixer->supports(new SplFileInfo('ExampleTest.php')));
        self::assertFalse($this->phpUnitMockChainLinesFixer->supports(new SplFileInfo('Example.php')));
        self::assertFalse($this->phpUnitMockChainLinesFixer->isCandidate(Tokens::fromCode('<?php $value = 1;')));
        self::assertTrue($this->phpUnitMockChainLinesFixer->isCandidate(Tokens::fromCode('<?php $mock->method();')));

        $fixerDefinition = $this->phpUnitMockChainLinesFixer->getDefinition();

        self::assertSame('Put each PHPUnit mock/stub chain method call on a separate, indented line.', $fixerDefinition->getSummary());
        self::assertCount(1, $fixerDefinition->getCodeSamples());
    }
}
