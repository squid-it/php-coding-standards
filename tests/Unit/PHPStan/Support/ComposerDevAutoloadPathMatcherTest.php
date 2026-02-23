<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support;

use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PHPStan\Support\ComposerDevAutoloadPathMatcher;
use Throwable;

final class ComposerDevAutoloadPathMatcherTest extends TestCase
{
    /** @var array<int, string> */
    private array $temporaryDirectoryPathList = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryDirectoryPathList as $temporaryDirectoryPath) {
            $this->removeDirectoryRecursively($temporaryDirectoryPath);
        }

        parent::tearDown();
    }

    /**
     * @throws Throwable
     */
    public function testIsFileInsideAutoloadDevPsr4DirectoryWithoutComposerJsonReturnsFalseSucceeds(): void
    {
        $projectDirectoryPath = $this->createTemporaryDirectoryPath();
        $filePath             = $projectDirectoryPath . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'Fixture.php';

        $this->createFile($filePath);

        $pathMatcher = new ComposerDevAutoloadPathMatcher();

        self::assertFalse($pathMatcher->isFileInsideAutoloadDevPsr4Directory($filePath));
    }

    /**
     * @throws Throwable
     */
    public function testIsFileInsideAutoloadDevPsr4DirectoryWithStringPathReturnsTrueSucceeds(): void
    {
        $projectDirectoryPath = $this->createTemporaryDirectoryPath();
        $this->writeComposerJson(
            $projectDirectoryPath,
            [
                'autoload-dev' => [
                    'psr-4' => [
                        'Fixture\Tests\\' => 'tests',
                    ],
                ],
            ],
        );

        $insideFilePath  = $projectDirectoryPath . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'Unit' . DIRECTORY_SEPARATOR . 'Fixture.php';
        $outsideFilePath = $projectDirectoryPath . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Fixture.php';
        $this->createFile($insideFilePath);
        $this->createFile($outsideFilePath);

        $pathMatcher = new ComposerDevAutoloadPathMatcher();

        self::assertTrue($pathMatcher->isFileInsideAutoloadDevPsr4Directory($insideFilePath));
        self::assertFalse($pathMatcher->isFileInsideAutoloadDevPsr4Directory($outsideFilePath));
    }

    /**
     * @throws Throwable
     */
    public function testIsFileInsideAutoloadDevPsr4DirectoryWithArrayPathReturnsTrueSucceeds(): void
    {
        $projectDirectoryPath = $this->createTemporaryDirectoryPath();
        $this->writeComposerJson(
            $projectDirectoryPath,
            [
                'autoload-dev' => [
                    'psr-4' => [
                        'Fixture\Tests\\' => [
                            'tests',
                            'fixtures/dev',
                        ],
                    ],
                ],
            ],
        );

        $filePath = $projectDirectoryPath
            . DIRECTORY_SEPARATOR
            . 'fixtures'
            . DIRECTORY_SEPARATOR
            . 'dev'
            . DIRECTORY_SEPARATOR
            . 'Fixture.php';
        $this->createFile($filePath);

        $pathMatcher = new ComposerDevAutoloadPathMatcher();

        self::assertTrue($pathMatcher->isFileInsideAutoloadDevPsr4Directory($filePath));
    }

    /**
     * @throws Throwable
     */
    public function testIsFileInsideAutoloadDevPsr4DirectoryWithAbsolutePathReturnsTrueSucceeds(): void
    {
        $projectDirectoryPath = $this->createTemporaryDirectoryPath();
        $absoluteTestsPath    = $projectDirectoryPath . DIRECTORY_SEPARATOR . 'absolute-tests';

        if (is_dir($absoluteTestsPath) === false) {
            $isAbsoluteTestsDirectoryCreated = mkdir($absoluteTestsPath, 0777, true);

            if ($isAbsoluteTestsDirectoryCreated === false) {
                self::fail('Failed to create absolute tests directory.');
            }
        }

        $resolvedAbsoluteTestsPath = realpath($absoluteTestsPath);

        if ($resolvedAbsoluteTestsPath === false) {
            self::fail('Failed to resolve absolute tests directory path.');
        }

        $this->writeComposerJson(
            $projectDirectoryPath,
            [
                'autoload-dev' => [
                    'psr-4' => [
                        'Fixture\Tests\\' => $resolvedAbsoluteTestsPath,
                    ],
                ],
            ],
        );

        $filePath = $absoluteTestsPath . DIRECTORY_SEPARATOR . 'Fixture.php';
        $this->createFile($filePath);

        $pathMatcher = new ComposerDevAutoloadPathMatcher();

        self::assertTrue($pathMatcher->isFileInsideAutoloadDevPsr4Directory($filePath));
    }

    private function createTemporaryDirectoryPath(): string
    {
        $temporaryDirectoryPath = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'composer-dev-path-matcher-test-'
            . uniqid('', true);

        if (is_dir($temporaryDirectoryPath) === false) {
            $isTemporaryDirectoryCreated = mkdir($temporaryDirectoryPath, 0777, true);

            if ($isTemporaryDirectoryCreated === false) {
                self::fail('Failed to create temporary directory.');
            }
        }

        $this->temporaryDirectoryPathList[] = $temporaryDirectoryPath;

        return $temporaryDirectoryPath;
    }

    private function createFile(string $filePath): void
    {
        $directoryPath = dirname($filePath);

        if (is_dir($directoryPath) === false) {
            $isDirectoryCreated = mkdir($directoryPath, 0777, true);

            if ($isDirectoryCreated === false) {
                self::fail(sprintf('Failed to create directory for file "%s".', $filePath));
            }
        }

        $writtenByteCount = file_put_contents($filePath, "<?php\n");

        if ($writtenByteCount === false) {
            self::fail(sprintf('Failed to create file "%s".', $filePath));
        }
    }

    /**
     * @param array<string, mixed> $composerConfiguration
     */
    private function writeComposerJson(string $projectDirectoryPath, array $composerConfiguration): void
    {
        $composerJsonPath          = $projectDirectoryPath . DIRECTORY_SEPARATOR . 'composer.json';
        $encodedComposerJsonString = json_encode(
            $composerConfiguration,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        );

        if ($encodedComposerJsonString === false) {
            self::fail('Failed to encode temporary composer configuration.');
        }

        $writtenByteCount = file_put_contents($composerJsonPath, $encodedComposerJsonString);

        if ($writtenByteCount === false) {
            self::fail('Failed to write temporary composer configuration.');
        }
    }

    private function removeDirectoryRecursively(string $directoryPath): void
    {
        if (is_dir($directoryPath) === false) {
            return;
        }

        $directoryEntryList = scandir($directoryPath);

        if ($directoryEntryList === false) {
            return;
        }

        foreach ($directoryEntryList as $directoryEntry) {
            if ($directoryEntry === '.' || $directoryEntry === '..') {
                continue;
            }

            $entryPath = $directoryPath . DIRECTORY_SEPARATOR . $directoryEntry;

            if (is_dir($entryPath) === true) {
                $this->removeDirectoryRecursively($entryPath);

                continue;
            }

            if (is_file($entryPath) === true) {
                unlink($entryPath);
            }
        }

        rmdir($directoryPath);
    }
}
