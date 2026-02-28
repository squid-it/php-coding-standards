<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture;

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_ as ParserClass;
use PHPStan\Analyser\NodeCallbackInvoker;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Reflection\ReflectionProviderStaticAccessor;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\LineRuleError;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPUnit\Framework\MockObject\Stub;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Architecture\NoServiceInstantiationRule;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime\RuntimeCustomException;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime\RuntimeHttpClient;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime\RuntimeInheritedMutableService;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime\RuntimeIslandService;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime\RuntimeNonFactoryConsumer;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime\RuntimeOrderDto;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime\RuntimePhpUnitTestCaseConsumer;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime\RuntimeProfileVo;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime\RuntimeReadonlyBehaviorService;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime\RuntimeServiceAssembler;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime\RuntimeServiceBuilder;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime\RuntimeServiceFactory;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime\RuntimeServiceProvider;
use Throwable;

final class NoServiceInstantiationRuleTest extends PHPStanTestCase
{
    private const string DIRECT_SERVICE_ERROR            = 'Instantiation of service "RuntimeHttpClient" is not allowed in non-creator class "RuntimeNonFactoryConsumer". Move creation to a class ending with "*Factory", "*Builder", or "*Provider" or inject the dependency.';
    private const string READONLY_BEHAVIOR_ERROR         = 'Instantiation of service "RuntimeReadonlyBehaviorService" is not allowed in non-creator class "RuntimeNonFactoryConsumer". Move creation to a class ending with "*Factory", "*Builder", or "*Provider" or inject the dependency.';
    private const string ISLAND_METHOD_ERROR             = 'Instantiation of service "RuntimeIslandService" is not allowed in non-creator class "RuntimeNonFactoryConsumer". Move creation to a class ending with "*Factory", "*Builder", or "*Provider" or inject the dependency.';
    private const string INHERITED_MUTABLE_SERVICE_ERROR = 'Instantiation of service "RuntimeInheritedMutableService" is not allowed in non-creator class "RuntimeNonFactoryConsumer". Move creation to a class ending with "*Factory", "*Builder", or "*Provider" or inject the dependency.';
    private const string PHPUNIT_TEST_CASE_SERVICE_ERROR = 'Instantiation of service "RuntimeHttpClient" is not allowed in non-creator class "RuntimePhpUnitTestCaseConsumer". Move creation to a class ending with "*Factory", "*Builder", or "*Provider" or inject the dependency.';

    private NoServiceInstantiationRule $rule;
    private ReflectionProvider $reflectionProvider;
    /** @var array<int, string> */
    private array $temporaryDirectoryPathList = [];

    protected function setUp(): void
    {
        parent::setUp();

        // @phpstan-ignore-next-line phpstanApi.method
        ReflectionProviderStaticAccessor::registerInstance(PHPStanTestCase::createReflectionProvider());
        $this->rule               = new NoServiceInstantiationRule();
        $this->reflectionProvider = PHPStanTestCase::createReflectionProvider();
    }

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
    public function testGetNodeTypeReturnsNewClassSucceeds(): void
    {
        self::assertSame(New_::class, (new NoServiceInstantiationRule())->getNodeType());
    }

    /**
     * @throws Throwable
     */
    public function testNonFactoryServiceInstantiationFails(): void
    {
        $line      = 30;
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode($line),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new ObjectType(RuntimeHttpClient::class),
            ),
        );

        self::assertCount(1, $errorList);

        $ruleError = $errorList[0];
        self::assertSame(self::DIRECT_SERVICE_ERROR, $ruleError->getMessage());

        if (($ruleError instanceof IdentifierRuleError) === false) {
            self::fail('Expected IdentifierRuleError implementation.');
        }

        self::assertSame('squidit.architecture.noServiceInstantiation', $ruleError->getIdentifier());

        if (($ruleError instanceof LineRuleError) === false) {
            self::fail('Expected LineRuleError implementation.');
        }

        self::assertSame($line, $ruleError->getLine());
    }

    /**
     * @throws Throwable
     */
    public function testFactoryScopeInstantiationSucceeds(): void
    {
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode(32),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeServiceFactory::class),
                new ObjectType(RuntimeHttpClient::class),
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testBuilderScopeInstantiationSucceeds(): void
    {
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode(33),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeServiceBuilder::class),
                new ObjectType(RuntimeHttpClient::class),
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testProviderScopeInstantiationSucceeds(): void
    {
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode(34),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeServiceProvider::class),
                new ObjectType(RuntimeHttpClient::class),
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testCustomCreatorSuffixConfigurationInstantiationSucceeds(): void
    {
        $customRule = new NoServiceInstantiationRule(['Assembler']);
        $errorList  = $customRule->processNode(
            $this->createNamedNewNode(35),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeServiceAssembler::class),
                new ObjectType(RuntimeHttpClient::class),
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testPhpUnitTestCaseScopeInstantiationIsIgnoredByDefaultSucceeds(): void
    {
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode(36),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimePhpUnitTestCaseConsumer::class),
                new ObjectType(RuntimeHttpClient::class),
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testPhpUnitTestCaseScopeInstantiationFailsWhenSkipDisabled(): void
    {
        $configuredRule = new NoServiceInstantiationRule(skipPhpUnitTestCaseClasses: false);
        $errorList      = $configuredRule->processNode(
            $this->createNamedNewNode(37),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimePhpUnitTestCaseConsumer::class),
                new ObjectType(RuntimeHttpClient::class),
            ),
        );

        self::assertCount(1, $errorList);
        self::assertSame(self::PHPUNIT_TEST_CASE_SERVICE_ERROR, $errorList[0]->getMessage());
    }

    /**
     * @throws Throwable
     */
    public function testExcludeComposerDevDirsEnabledSkipsAutoloadDevPsr4PathSucceeds(): void
    {
        $fixtureFilePath = $this->createTemporaryAutoloadDevFixtureFilePath();
        $configuredRule  = new NoServiceInstantiationRule(excludeComposerDevDirs: true);
        $errorList       = $configuredRule->processNode(
            $this->createNamedNewNode(38),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new ObjectType(RuntimeHttpClient::class),
                filePath: $fixtureFilePath,
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testExcludeComposerDevDirsDisabledStillReportsAutoloadDevPsr4PathFails(): void
    {
        $fixtureFilePath = $this->createTemporaryAutoloadDevFixtureFilePath();
        $errorList       = $this->rule->processNode(
            $this->createNamedNewNode(39),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new ObjectType(RuntimeHttpClient::class),
                filePath: $fixtureFilePath,
            ),
        );

        self::assertCount(1, $errorList);
        self::assertSame(self::DIRECT_SERVICE_ERROR, $errorList[0]->getMessage());
    }

    /**
     * @throws Throwable
     */
    public function testPromotedConstructorParameterDefaultInstantiationIsIgnoredSucceeds(): void
    {
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode(37),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new ObjectType(RuntimeHttpClient::class),
                null,
                true,
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testConstructorBodyInstantiationStillFails(): void
    {
        $line      = 39;
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode($line),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new ObjectType(RuntimeHttpClient::class),
                '__construct',
                true,
            ),
        );

        self::assertCount(1, $errorList);
        self::assertSame(self::DIRECT_SERVICE_ERROR, $errorList[0]->getMessage());
    }

    /**
     * @throws Throwable
     */
    public function testVoDtoInstantiationSucceeds(): void
    {
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode(34),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new ObjectType(RuntimeOrderDto::class),
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testReadonlyPropertiesVoInstantiationSucceeds(): void
    {
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode(36),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new ObjectType(RuntimeProfileVo::class),
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testReadonlyBehaviorServiceInstantiationFails(): void
    {
        $line      = 38;
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode($line),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new ObjectType(RuntimeReadonlyBehaviorService::class),
            ),
        );

        self::assertCount(1, $errorList);
        self::assertSame(self::READONLY_BEHAVIOR_ERROR, $errorList[0]->getMessage());

        if (($errorList[0] instanceof LineRuleError) === false) {
            self::fail('Expected LineRuleError implementation.');
        }

        self::assertSame($line, $errorList[0]->getLine());
    }

    /**
     * @throws Throwable
     */
    public function testInternalClassInstantiationSucceeds(): void
    {
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode(40),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new ObjectType(\DateTimeImmutable::class),
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testThrowableClassInstantiationSucceeds(): void
    {
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode(41),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new ObjectType(RuntimeCustomException::class),
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testGlobalScopeInstantiationIsIgnoredSucceeds(): void
    {
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode(42),
            $this->createScopeStub(
                null,
                new ObjectType(RuntimeHttpClient::class),
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testDynamicTypeWithoutObjectClassIsIgnoredSucceeds(): void
    {
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode(44),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new MixedType(),
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testAnonymousClassInstantiationIsIgnoredSucceeds(): void
    {
        $anonymousClassNewNode = new New_(
            class: new ParserClass(
                name: null,
                subNodes: ['stmts' => []],
                attributes: ['startLine' => 46],
            ),
            args: [],
            attributes: ['startLine' => 46],
        );
        $errorList = $this->rule->processNode(
            $anonymousClassNewNode,
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new MixedType(),
            ),
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testReadonlyClassWithIslandMethodFails(): void
    {
        $line      = 48;
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode($line),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new ObjectType(RuntimeIslandService::class),
            ),
        );

        self::assertCount(1, $errorList);
        self::assertSame(self::ISLAND_METHOD_ERROR, $errorList[0]->getMessage());
    }

    /**
     * @throws Throwable
     */
    public function testInheritedMutablePropertyServiceFails(): void
    {
        $line      = 50;
        $errorList = $this->rule->processNode(
            $this->createNamedNewNode($line),
            $this->createScopeStub(
                $this->resolveClassReflection(RuntimeNonFactoryConsumer::class),
                new ObjectType(RuntimeInheritedMutableService::class),
            ),
        );

        self::assertCount(1, $errorList);
        self::assertSame(self::INHERITED_MUTABLE_SERVICE_ERROR, $errorList[0]->getMessage());
    }

    private function createNamedNewNode(int $line): New_
    {
        return new New_(
            class: new Name('RuntimeType'),
            args: [],
            attributes: ['startLine' => $line],
        );
    }

    private function resolveClassReflection(string $className): ClassReflection
    {
        return $this->reflectionProvider->getClass($className);
    }

    private function createScopeStub(
        ?ClassReflection $classReflection,
        Type $newType,
        ?string $functionName = null,
        bool $isInClass = false,
        string $filePath = __FILE__,
    ): Scope&NodeCallbackInvoker {
        /** @var NodeCallbackInvoker&Scope&Stub $scope */
        $scope = self::createStubForIntersectionOfInterfaces([Scope::class, NodeCallbackInvoker::class]);
        $scope->method('getClassReflection')->willReturn($classReflection);
        $scope->method('getType')->willReturn($newType);
        $scope->method('getFunctionName')->willReturn($functionName);
        $scope->method('isInClass')->willReturn($isInClass);
        $scope->method('getFile')->willReturn($filePath);

        return $scope;
    }

    private function createTemporaryAutoloadDevFixtureFilePath(): string
    {
        $temporaryProjectDirectoryPath = $this->createTemporaryProjectDirectoryPath();
        $this->writeComposerJsonWithAutoloadDevTestsPath($temporaryProjectDirectoryPath);

        $fixtureDirectoryPath = $temporaryProjectDirectoryPath . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'Fixtures';

        if (is_dir($fixtureDirectoryPath) === false) {
            $isFixtureDirectoryCreated = mkdir($fixtureDirectoryPath, 0777, true);

            if ($isFixtureDirectoryCreated === false) {
                self::fail('Failed to create temporary fixture directory for NoServiceInstantiationRule tests.');
            }
        }

        $fixtureFilePath  = $fixtureDirectoryPath . DIRECTORY_SEPARATOR . 'FixtureFile.php';
        $writtenByteCount = file_put_contents($fixtureFilePath, "<?php\n");

        if ($writtenByteCount === false) {
            self::fail('Failed to write temporary fixture file for NoServiceInstantiationRule tests.');
        }

        return $fixtureFilePath;
    }

    private function createTemporaryProjectDirectoryPath(): string
    {
        $temporaryDirectoryPath = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'no-service-instantiation-rule-'
            . uniqid('', true);

        if (is_dir($temporaryDirectoryPath) === false) {
            $isTemporaryDirectoryCreated = mkdir($temporaryDirectoryPath, 0777, true);

            if ($isTemporaryDirectoryCreated === false) {
                self::fail('Failed to create temporary project directory for NoServiceInstantiationRule tests.');
            }
        }

        $this->temporaryDirectoryPathList[] = $temporaryDirectoryPath;

        return $temporaryDirectoryPath;
    }

    private function writeComposerJsonWithAutoloadDevTestsPath(string $projectRootPath): void
    {
        $composerJsonPath = $projectRootPath . DIRECTORY_SEPARATOR . 'composer.json';
        $writtenByteCount = file_put_contents(
            $composerJsonPath,
            <<<'JSON'
                {
                    "autoload-dev": {
                        "psr-4": {
                            "Fixture\\Tests\\": "tests"
                        }
                    }
                }
                JSON,
        );

        if ($writtenByteCount === false) {
            self::fail('Failed to write temporary composer.json for NoServiceInstantiationRule tests.');
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
