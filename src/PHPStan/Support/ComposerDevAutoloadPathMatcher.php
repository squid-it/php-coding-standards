<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Support;

final class ComposerDevAutoloadPathMatcher
{
    /** @var array<string, bool> */
    private array $isFileInsideAutoloadDevDirectoryByPathCache = [];

    /** @var array<string, ?string> */
    private array $projectRootByDirectoryPathCache = [];

    /** @var array<string, array<int, string>> */
    private array $autoloadDevDirectoryPathListByProjectRootCache = [];

    public function isFileInsideAutoloadDevPsr4Directory(string $filePath): bool
    {
        $normalizedFilePath = $this->normalizePath($filePath);

        if (array_key_exists($normalizedFilePath, $this->isFileInsideAutoloadDevDirectoryByPathCache) === true) {
            return $this->isFileInsideAutoloadDevDirectoryByPathCache[$normalizedFilePath];
        }

        $projectRootPath = $this->resolveNearestProjectRootPath(dirname($normalizedFilePath));

        if ($projectRootPath === null) {
            $this->isFileInsideAutoloadDevDirectoryByPathCache[$normalizedFilePath] = false;

            return false;
        }

        $autoloadDevDirectoryPathList = $this->resolveAutoloadDevDirectoryPathList($projectRootPath);

        foreach ($autoloadDevDirectoryPathList as $autoloadDevDirectoryPath) {
            if ($this->isFilePathInsideDirectoryPath($normalizedFilePath, $autoloadDevDirectoryPath) === true) {
                $this->isFileInsideAutoloadDevDirectoryByPathCache[$normalizedFilePath] = true;

                return true;
            }
        }

        $this->isFileInsideAutoloadDevDirectoryByPathCache[$normalizedFilePath] = false;

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function resolveAutoloadDevDirectoryPathList(string $projectRootPath): array
    {
        $normalizedProjectRootPath = $this->normalizePath($projectRootPath);

        if (array_key_exists($normalizedProjectRootPath, $this->autoloadDevDirectoryPathListByProjectRootCache) === true) {
            return $this->autoloadDevDirectoryPathListByProjectRootCache[$normalizedProjectRootPath];
        }

        $composerConfiguration = $this->readComposerConfiguration(
            $normalizedProjectRootPath . DIRECTORY_SEPARATOR . 'composer.json',
        );

        if ($composerConfiguration === null) {
            $this->autoloadDevDirectoryPathListByProjectRootCache[$normalizedProjectRootPath] = [];

            return [];
        }

        $autoloadDevSection = $composerConfiguration['autoload-dev'] ?? null;

        if (is_array($autoloadDevSection) === false) {
            $this->autoloadDevDirectoryPathListByProjectRootCache[$normalizedProjectRootPath] = [];

            return [];
        }

        $autoloadDevPsr4NamespaceMap = $autoloadDevSection['psr-4'] ?? null;

        if (is_array($autoloadDevPsr4NamespaceMap) === false) {
            $this->autoloadDevDirectoryPathListByProjectRootCache[$normalizedProjectRootPath] = [];

            return [];
        }

        $autoloadDevDirectoryPathList = [];

        foreach ($autoloadDevPsr4NamespaceMap as $autoloadDevDirectoryPathValue) {
            $this->addAutoloadDevDirectoryPathValue(
                $autoloadDevDirectoryPathList,
                $autoloadDevDirectoryPathValue,
                $normalizedProjectRootPath,
            );
        }

        $this->autoloadDevDirectoryPathListByProjectRootCache[$normalizedProjectRootPath] = $autoloadDevDirectoryPathList;

        return $autoloadDevDirectoryPathList;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readComposerConfiguration(string $composerJsonPath): ?array
    {
        if (is_file($composerJsonPath) === false) {
            return null;
        }

        $composerConfigurationJson = file_get_contents($composerJsonPath);

        if ($composerConfigurationJson === false) {
            return null;
        }

        $composerConfiguration = json_decode($composerConfigurationJson, true);

        if (is_array($composerConfiguration) === false) {
            return null;
        }

        /** @var array<string, mixed> $composerConfiguration */
        return $composerConfiguration;
    }

    /**
     * @param array<int, string> $autoloadDevDirectoryPathList
     */
    private function addAutoloadDevDirectoryPathValue(
        array &$autoloadDevDirectoryPathList,
        mixed $autoloadDevDirectoryPathValue,
        string $projectRootPath,
    ): void {
        if (is_string($autoloadDevDirectoryPathValue) === true) {
            $this->addAutoloadDevDirectoryPath(
                $autoloadDevDirectoryPathList,
                $autoloadDevDirectoryPathValue,
                $projectRootPath,
            );

            return;
        }

        if (is_array($autoloadDevDirectoryPathValue) === false) {
            return;
        }

        foreach ($autoloadDevDirectoryPathValue as $autoloadDevDirectoryPathItem) {
            if (is_string($autoloadDevDirectoryPathItem) === false) {
                continue;
            }

            $this->addAutoloadDevDirectoryPath(
                $autoloadDevDirectoryPathList,
                $autoloadDevDirectoryPathItem,
                $projectRootPath,
            );
        }
    }

    /**
     * @param array<int, string> $autoloadDevDirectoryPathList
     */
    private function addAutoloadDevDirectoryPath(
        array &$autoloadDevDirectoryPathList,
        string $autoloadDevDirectoryPath,
        string $projectRootPath,
    ): void {
        $trimmedAutoloadDevDirectoryPath = trim($autoloadDevDirectoryPath);

        if ($trimmedAutoloadDevDirectoryPath === '') {
            return;
        }

        $resolvedAutoloadDevDirectoryPath = $this->resolveComposerPath(
            $projectRootPath,
            $trimmedAutoloadDevDirectoryPath,
        );

        if (
            $this->isPathInList(
                $autoloadDevDirectoryPathList,
                $resolvedAutoloadDevDirectoryPath,
            ) === true
        ) {
            return;
        }

        $autoloadDevDirectoryPathList[] = $resolvedAutoloadDevDirectoryPath;
    }

    private function resolveComposerPath(string $projectRootPath, string $composerPath): string
    {
        if ($this->isAbsolutePath($composerPath) === true) {
            return $this->normalizePath($composerPath);
        }

        return $this->normalizePath($projectRootPath . DIRECTORY_SEPARATOR . $composerPath);
    }

    private function isAbsolutePath(string $path): bool
    {
        if (preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1) {
            return true;
        }

        if (str_starts_with($path, '\\\\') === true) {
            return true;
        }

        return str_starts_with($path, '/');
    }

    /**
     * @param array<int, string> $pathList
     */
    private function isPathInList(array $pathList, string $searchPath): bool
    {
        $searchPathForComparison = $this->toComparisonPath($searchPath);

        foreach ($pathList as $path) {
            if ($this->toComparisonPath($path) === $searchPathForComparison) {
                return true;
            }
        }

        return false;
    }

    private function isFilePathInsideDirectoryPath(string $filePath, string $directoryPath): bool
    {
        $normalizedFilePath      = $this->normalizePath($filePath);
        $normalizedDirectoryPath = $this->normalizePath($directoryPath);
        $filePathForComparison   = $this->toComparisonPath($normalizedFilePath);
        $directoryPathForCompare = $this->toComparisonPath($normalizedDirectoryPath);

        if ($filePathForComparison === $directoryPathForCompare) {
            return true;
        }

        if (str_ends_with($directoryPathForCompare, DIRECTORY_SEPARATOR) === true) {
            return str_starts_with($filePathForComparison, $directoryPathForCompare);
        }

        return str_starts_with($filePathForComparison, $directoryPathForCompare . DIRECTORY_SEPARATOR);
    }

    private function normalizePath(string $path): string
    {
        $realPath       = realpath($path);
        $normalizedPath = $realPath !== false ? $realPath : $path;

        $normalizedPath = str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath);
        $normalizedPath = str_replace('\\', DIRECTORY_SEPARATOR, $normalizedPath);

        return $this->trimTrailingDirectorySeparators($normalizedPath);
    }

    private function trimTrailingDirectorySeparators(string $path): string
    {
        $trimmedPath = rtrim($path, "\\/");

        if ($trimmedPath === '') {
            return DIRECTORY_SEPARATOR;
        }

        if (preg_match('/^[A-Za-z]:$/', $trimmedPath) === 1) {
            return $trimmedPath . DIRECTORY_SEPARATOR;
        }

        return $trimmedPath;
    }

    private function toComparisonPath(string $path): string
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return strtolower($path);
        }

        return $path;
    }

    private function resolveNearestProjectRootPath(string $directoryPath): ?string
    {
        $currentDirectoryPath     = $this->normalizePath($directoryPath);
        $visitedDirectoryPathList = [];

        while (true) {
            $visitedDirectoryPathList[] = $currentDirectoryPath;

            if (array_key_exists($currentDirectoryPath, $this->projectRootByDirectoryPathCache) === true) {
                $projectRootPath = $this->projectRootByDirectoryPathCache[$currentDirectoryPath];

                $this->cacheProjectRootForVisitedDirectoryList($visitedDirectoryPathList, $projectRootPath);

                return $projectRootPath;
            }

            $composerJsonPath = $currentDirectoryPath . DIRECTORY_SEPARATOR . 'composer.json';

            if (is_file($composerJsonPath) === true) {
                $this->cacheProjectRootForVisitedDirectoryList(
                    $visitedDirectoryPathList,
                    $currentDirectoryPath,
                );

                return $currentDirectoryPath;
            }

            $parentDirectoryPath = $this->normalizePath(dirname($currentDirectoryPath));

            if ($parentDirectoryPath === $currentDirectoryPath) {
                $this->cacheProjectRootForVisitedDirectoryList($visitedDirectoryPathList, null);

                return null;
            }

            $currentDirectoryPath = $parentDirectoryPath;
        }
    }

    /**
     * @param array<int, string> $visitedDirectoryPathList
     */
    private function cacheProjectRootForVisitedDirectoryList(
        array $visitedDirectoryPathList,
        ?string $projectRootPath,
    ): void {
        foreach ($visitedDirectoryPathList as $visitedDirectoryPath) {
            $this->projectRootByDirectoryPathCache[$visitedDirectoryPath] = $projectRootPath;
        }
    }
}
