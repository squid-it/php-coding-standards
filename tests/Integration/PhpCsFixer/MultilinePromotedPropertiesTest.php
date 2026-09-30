<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Integration\PhpCsFixer;

use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SquidIT\PhpCodingStandards\PhpCsFixer\Rules;
use Throwable;

/**
 * Runs the real php-cs-fixer binary with the full company rule set against fixture files,
 * proving that promoted constructor properties always start on a new line.
 */
final class MultilinePromotedPropertiesTest extends TestCase
{
    private const string FIXTURE_DIRECTORY     = __DIR__ . '/Fixtures/MultilinePromotedProperties';
    private const int MAXIMUM_FIXER_PASS_COUNT = 3;

    private string $workingFilePath;

    protected function setUp(): void
    {
        parent::setUp();

        $workingFilePath = tempnam(sys_get_temp_dir(), 'squidit-cs-');

        if ($workingFilePath === false) {
            throw new RuntimeException('Unable to create temporary file for php-cs-fixer integration test');
        }

        $this->workingFilePath = $workingFilePath . '.php';
        unlink($workingFilePath);
    }

    protected function tearDown(): void
    {
        if (is_file($this->workingFilePath) === true) {
            unlink($this->workingFilePath);
        }

        parent::tearDown();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function fixtureNameProvider(): array
    {
        $fixtureNameList   = [];
        $inputFilePathList = glob(self::FIXTURE_DIRECTORY . '/Input/*.php');

        if ($inputFilePathList === false) {
            return $fixtureNameList;
        }

        foreach ($inputFilePathList as $inputFilePath) {
            $fixtureName                   = basename($inputFilePath, '.php');
            $fixtureNameList[$fixtureName] = [$fixtureName];
        }

        return $fixtureNameList;
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('fixtureNameProvider')]
    public function testFixInputProducesExpectedOutputSucceeds(string $fixtureName): void
    {
        $inputCode    = $this->readFile(self::FIXTURE_DIRECTORY . '/Input/' . $fixtureName . '.php');
        $expectedCode = $this->readFile(self::FIXTURE_DIRECTORY . '/Expected/' . $fixtureName . '.php');

        self::assertSame($expectedCode, $this->fixCodeUntilStable($inputCode));
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('fixtureNameProvider')]
    public function testFixExpectedOutputIsIdempotentSucceeds(string $fixtureName): void
    {
        $expectedCode = $this->readFile(self::FIXTURE_DIRECTORY . '/Expected/' . $fixtureName . '.php');

        self::assertSame($expectedCode, $this->fixCode($expectedCode));
    }

    /**
     * Upstream `multiline_promoted_properties` runs after `method_argument_space`, so attributes on promoted
     * properties are only moved to their own line (attribute_placement: standalone) by a second fixer run.
     *
     * @throws JsonException
     * @throws RuntimeException
     */
    private function fixCodeUntilStable(string $code): string
    {
        for ($passNumber = 1; $passNumber <= self::MAXIMUM_FIXER_PASS_COUNT; $passNumber++) {
            $fixedCode = $this->fixCode($code);

            if ($fixedCode === $code) {
                return $fixedCode;
            }

            $code = $fixedCode;
        }

        throw new RuntimeException(sprintf(
            'php-cs-fixer output did not stabilize after %d passes for file: %s',
            self::MAXIMUM_FIXER_PASS_COUNT,
            $this->workingFilePath,
        ));
    }

    /**
     * @throws JsonException
     * @throws RuntimeException
     */
    private function fixCode(string $code): string
    {
        file_put_contents($this->workingFilePath, $code);

        $command = [
            PHP_BINARY,
            dirname(__DIR__, 3) . '/vendor/friendsofphp/php-cs-fixer/php-cs-fixer',
            'fix',
            $this->workingFilePath,
            '--rules=' . json_encode(Rules::getRules(), JSON_THROW_ON_ERROR),
            '--allow-risky=yes',
            '--allow-unsupported-php-version=yes',
            '--using-cache=no',
            '--no-interaction',
        ];

        $environmentVariableList                = getenv();
        $environmentVariableList['XDEBUG_MODE'] = 'off';

        $pipeList = [];
        $process  = proc_open(
            $command,
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipeList,
            null,
            $environmentVariableList,
        );

        if (is_resource($process) === false) {
            throw new RuntimeException('Unable to start php-cs-fixer process for file: ' . $this->workingFilePath);
        }

        $output      = (string) stream_get_contents($pipeList[1]);
        $errorOutput = (string) stream_get_contents($pipeList[2]);
        fclose($pipeList[1]);
        fclose($pipeList[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException(sprintf(
                'php-cs-fixer exited with code %d for file: %s, output: %s, error output: %s',
                $exitCode,
                $this->workingFilePath,
                $output,
                $errorOutput,
            ));
        }

        return $this->readFile($this->workingFilePath);
    }

    /**
     * @throws RuntimeException
     */
    private function readFile(string $filePath): string
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            throw new RuntimeException('Unable to read file: ' . $filePath);
        }

        return $content;
    }
}
