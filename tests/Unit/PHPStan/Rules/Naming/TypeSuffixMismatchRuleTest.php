<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming;

use PhpParser\Modifiers;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType as ParserUnionType;
use PHPStan\Analyser\NodeCallbackInvoker;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\LineRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\MockObject\Stub;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\TypeSuffixMismatchRule;
use SquidIT\PhpCodingStandards\PHPStan\Support\DenyList;
use SquidIT\PhpCodingStandards\PHPStan\Support\TypeCandidateResolver;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming\Runtime\AbstractChannel as RuntimeAbstractChannel;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming\Runtime\ChannelInterface as RuntimeChannelInterface;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming\Runtime\DenyListedConcreteClass as RuntimeDenyListedConcreteClass;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming\Runtime\DenyListedInterface as RuntimeDenyListedInterface;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming\Runtime\DuplicateBaseName\A\ChannelInterface as RuntimeAChannelInterface;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming\Runtime\DuplicateBaseName\B\ChannelInterface as RuntimeBChannelInterface;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming\Runtime\FooInterface as RuntimeFooInterface;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming\Runtime\MyService as RuntimeMyService;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming\Runtime\ReadChannel as RuntimeReadChannel;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming\Runtime\TransportInterface as RuntimeTransportInterface;
use Throwable;
use TypeSuffixMismatchFixtures\Invalid\DenyListRegression\DenyListedInterface;

/**
 * @extends RuleTestCase<TypeSuffixMismatchRule>
 */
final class TypeSuffixMismatchRuleTest extends RuleTestCase
{
    private const string FIXTURES_DIR                                                      = __DIR__ . '/Fixtures/TypeSuffixMismatch';
    private const string INVALID_FOO_DATA_FILE                                             = self::FIXTURES_DIR . '/Invalid/FooData.php';
    private const string INVALID_BAR_DATA_FILE                                             = self::FIXTURES_DIR . '/Invalid/BarData.php';
    private const string INVALID_CHANNEL_INTERFACE_FILE                                    = self::FIXTURES_DIR . '/Invalid/ChannelInterface.php';
    private const string INVALID_INTERFACE_BARE_NAME_FILE                                  = self::FIXTURES_DIR . '/Invalid/InterfaceBareName.php';
    private const string VALID_ABSTRACT_SERVICE_MESSAGE_FILE                               = self::FIXTURES_DIR . '/Valid/AbstractServiceMessage.php';
    private const string VALID_CHANNEL_INTERFACE_FILE                                      = self::FIXTURES_DIR . '/Valid/ChannelInterface.php';
    private const string VALID_FOO_DATA_FILE                                               = self::FIXTURES_DIR . '/Valid/FooData.php';
    private const string VALID_BAR_DATA_FILE                                               = self::FIXTURES_DIR . '/Valid/BarData.php';
    private const string VALID_DYNAMIC_ASSIGNMENT_FILE                                     = self::FIXTURES_DIR . '/Valid/EdgeCases/AssignmentWithDynamicVariableName.php';
    private const string VALID_INLINE_VAR_ASSIGNMENT_FILE                                  = self::FIXTURES_DIR . '/Valid/EdgeCases/InlineVarAnnotationNarrowsAssignmentType.php';
    private const string VALID_TYPED_PROPERTY_DOCBLOCK_FILE                                = self::FIXTURES_DIR . '/Valid/EdgeCases/TypedPropertyDocblockNarrowsType.php';
    private const string VALID_PROMOTED_PROPERTY_DOCBLOCK_FILE                             = self::FIXTURES_DIR . '/Valid/EdgeCases/PromotedPropertyDocblockNarrowsType.php';
    private const string VALID_PROMOTED_PROPERTY_PARAM_DOCBLOCK_FILE                       = self::FIXTURES_DIR . '/Valid/EdgeCases/PromotedPropertyParamDocblockNarrowsType.php';
    private const string INVALID_PROMOTED_PROPERTY_PARAM_DOCBLOCK_NO_NARROWING_FILE        = self::FIXTURES_DIR . '/Invalid/EdgeCases/PromotedPropertyParamDocblockNoNarrowingMismatch.php';
    private const string VALID_PROMOTED_PROPERTY_PARAM_DOCBLOCK_PARENT_INTERFACE_NAME_FILE = self::FIXTURES_DIR . '/Valid/EdgeCases/PromotedPropertyParamDocblockParentInterfaceNameValid.php';
    private const string VALID_SCALAR_ASSIGNMENT_FILE                                      = self::FIXTURES_DIR . '/Valid/EdgeCases/ScalarAssignmentNoObjectType.php';
    private const string VALID_NO_OBJECT_TYPES_FILE                                        = self::FIXTURES_DIR . '/Valid/EdgeCases/NoObjectTypedProperties.php';
    private const string VALID_DNF_TYPE_FILE                                               = self::FIXTURES_DIR . '/Valid/EdgeCases/DnfTypedPropertyIgnored.php';
    private const string VALID_DUPLICATE_A_FILE                                            = self::FIXTURES_DIR . '/Valid/EdgeCases/DuplicateBaseName/A/ChannelInterface.php';
    private const string VALID_DUPLICATE_B_FILE                                            = self::FIXTURES_DIR . '/Valid/EdgeCases/DuplicateBaseName/B/ChannelInterface.php';
    private const string VALID_GLOBAL_FOO_DATA_FILE                                        = self::FIXTURES_DIR . '/Valid/EdgeCases/GlobalFooData.php';
    private const string INVALID_NULLABLE_FILE                                             = self::FIXTURES_DIR . '/Invalid/EdgeCases/NullableTypedPropertyMismatch.php';
    private const string INVALID_UNION_PROPERTY_FILE                                       = self::FIXTURES_DIR . '/Invalid/EdgeCases/UnionTypedPropertyMismatch.php';
    private const string INVALID_DUPLICATE_INTERFACE_BASE_NAME_FILE                        = self::FIXTURES_DIR . '/Invalid/EdgeCases/DuplicateInterfaceBaseName.php';
    private const string INVALID_GLOBAL_TYPE_FILE                                          = self::FIXTURES_DIR . '/Invalid/EdgeCases/GlobalClassTypedPropertyMismatch.php';
    private const string DENYLIST_INTERFACE_FILE                                           = self::FIXTURES_DIR . '/Invalid/DenyListRegression/DenyListedInterface.php';
    private const string DENYLIST_CONCRETE_FILE                                            = self::FIXTURES_DIR . '/Invalid/DenyListRegression/DenyListedConcreteClass.php';
    private const string DENYLIST_FIXTURE_FILE                                             = self::FIXTURES_DIR . '/Invalid/DenyListRegression/DenyListDoubleErrorCandidate.php';
    private const string INVALID_INTERSECTION_PROPERTY_FILE                                = self::FIXTURES_DIR . '/Invalid/EdgeCases/IntersectionTypedPropertyMismatch.php';
    private const string INVALID_INTERSECTION_PROMOTED_PROPERTY_FILE                       = self::FIXTURES_DIR . '/Invalid/EdgeCases/IntersectionPromotedPropertyMismatch.php';
    private const string VALID_INTERSECTION_INLINE_VAR_ASSIGNMENT_FILE                     = self::FIXTURES_DIR . '/Valid/EdgeCases/IntersectionInlineVarAnnotationNarrowsAssignmentType.php';
    private const string VALID_INTERSECTION_TYPED_PROPERTY_DOCBLOCK_FILE                   = self::FIXTURES_DIR . '/Valid/EdgeCases/IntersectionTypedPropertyDocblockNarrowsType.php';
    private const string VALID_INTERSECTION_PROMOTED_PROPERTY_DOCBLOCK_FILE                = self::FIXTURES_DIR . '/Valid/EdgeCases/IntersectionPromotedPropertyDocblockNarrowsType.php';
    private const string FOO_DATA_MISMATCH_MESSAGE                                         = 'Name "$service" does not match inferred type "FooData". Allowed base names: fooData. Use one of these names directly or a contextual prefix ending with: FooData.';
    private const string ASSIGNMENT_MISMATCH_MESSAGE                                       = 'Name "$item" does not match inferred type "FooData". Allowed base names: fooData. Use one of these names directly or a contextual prefix ending with: FooData.';
    private const string UNION_MISMATCH_MESSAGE                                            = 'Name "$item" does not match inferred type "BarData|FooData". Allowed base names: barData, fooData. Use one of these names directly or a contextual prefix ending with: BarData, FooData.';
    private const string UNION_PROPERTY_MISMATCH                                           = 'Name "$service" does not match inferred type "BarData|FooData". Allowed base names: barData, fooData. Use one of these names directly or a contextual prefix ending with: BarData, FooData.';
    private const string DNF_UNION_MISMATCH                                                = 'Name "$service" does not match inferred type "BarData|ChannelInterface|FooData". Allowed base names: barData, channel, fooData. Use one of these names directly or a contextual prefix ending with: BarData, Channel, FooData.';
    private const string INTERFACE_BARE_NAME_MESSAGE                                       = 'Interface-typed name "$channel" uses the bare interface base name "channel" (inferred interface type: ChannelInterface). Prefer a contextual prefix like "$readChannel".';
    private const string INTERFACE_BARE_NAME_IDENTIFIER                                    = 'squidit.naming.interfaceBareName';
    private const string TYPE_SUFFIX_MISMATCH_IDENTIFIER                                   = 'squidit.naming.typeSuffixMismatch';
    private const string DENYLIST_MISMATCH_MESSAGE                                         = 'Name "$denyListed" does not match inferred type "DenyListedConcreteClass". Allowed base names: denyListedConcreteClass. Use one of these names directly or a contextual prefix ending with: DenyListedConcreteClass.';
    private const string GLOBAL_FOO_DATA_MISMATCH                                          = 'Name "$service" does not match inferred type "GlobalFooData". Allowed base names: globalFooData. Use one of these names directly or a contextual prefix ending with: GlobalFooData.';
    private const string INTERSECTION_PROPERTY_MISMATCH                                    = 'Name "$service" does not match inferred type "ChannelInterface|FooData". Allowed base names: channel, fooData. Use one of these names directly or a contextual prefix ending with: Channel, FooData.';
    private const string CONTAINER_INTERFACE_MISMATCH                                      = 'Name "$containerMason" does not match inferred type "ContainerInterface". Allowed base names: container. Use one of these names directly or a contextual prefix ending with: Container.';

    private bool $isCoverageModeEnabled;
    private DenyList $denyList;
    /** @var array<string, string> */
    private array $resolvedTypeNameMap = [];

    protected function setUp(): void
    {
        parent::setUp();

        $xdebugMode = getenv('XDEBUG_MODE');

        $this->isCoverageModeEnabled = $xdebugMode !== false && str_contains((string) $xdebugMode, 'coverage');
        $this->denyList              = new DenyList();
        $this->resolvedTypeNameMap   = [];
    }

    protected function getRule(): Rule
    {
        return new TypeSuffixMismatchRule(
            typeCandidateResolver: new TypeCandidateResolver(
                denyList: $this->denyList,
            ),
        );
    }

    /**
     * @return array<int, string>
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/TypeSuffixMismatchRuleTest.neon'];
    }

    /**
     * @throws Throwable
     */
    public function testValidScenariosSucceeds(): void
    {
        $this->analyse([
            self::VALID_CHANNEL_INTERFACE_FILE,
            self::VALID_FOO_DATA_FILE,
            self::VALID_BAR_DATA_FILE,
            self::FIXTURES_DIR . '/Valid/ValidScenarios.php',
        ], []);
    }

    /**
     * @throws Throwable
     */
    public function testAbstractPrefixNamingSucceeds(): void
    {
        $this->analyse([
            self::VALID_ABSTRACT_SERVICE_MESSAGE_FILE,
            self::FIXTURES_DIR . '/Valid/EdgeCases/AbstractPrefixNamingIsValid.php',
        ], []);
    }

    /**
     * @throws Throwable
     */
    public function testDynamicVariableAssignmentWithObjectTypeSucceeds(): void
    {
        $this->analyse([
            self::VALID_FOO_DATA_FILE,
            self::VALID_DYNAMIC_ASSIGNMENT_FILE,
        ], []);
    }

    /**
     * @throws Throwable
     */
    public function testInlineVarAnnotationNarrowsAssignmentTypeSucceeds(): void
    {
        $this->analyse([self::VALID_INLINE_VAR_ASSIGNMENT_FILE], []);
    }

    /**
     * @throws Throwable
     */
    public function testTypedPropertyDocblockNarrowsTypeSucceeds(): void
    {
        $this->analyse([self::VALID_TYPED_PROPERTY_DOCBLOCK_FILE], []);
    }

    /**
     * @throws Throwable
     */
    public function testPromotedPropertyDocblockNarrowsTypeSucceeds(): void
    {
        $this->analyse([self::VALID_PROMOTED_PROPERTY_DOCBLOCK_FILE], []);
    }

    /**
     * @throws Throwable
     */
    public function testPromotedPropertyParamDocblockNarrowsTypeSucceeds(): void
    {
        $this->skipCoverageUnstablePromotedPropertyParamDocblockFixture();
        $this->analyse([self::VALID_PROMOTED_PROPERTY_PARAM_DOCBLOCK_FILE], []);
    }

    /**
     * @throws Throwable
     */
    public function testPromotedPropertyParamDocblockWithoutNarrowingFails(): void
    {
        $this->skipCoverageUnstablePromotedPropertyParamDocblockFixture();
        $this->analyse([self::INVALID_PROMOTED_PROPERTY_PARAM_DOCBLOCK_NO_NARROWING_FILE], [
            [self::CONTAINER_INTERFACE_MISMATCH, 15],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testPromotedPropertyParamDocblockWithParentInterfaceNameSucceeds(): void
    {
        $this->skipCoverageUnstablePromotedPropertyParamDocblockFixture();
        $this->analyse([self::VALID_PROMOTED_PROPERTY_PARAM_DOCBLOCK_PARENT_INTERFACE_NAME_FILE], []);
    }

    /**
     * @throws Throwable
     */
    public function testScalarAssignmentWithoutObjectTypeSucceeds(): void
    {
        $this->analyse([self::VALID_SCALAR_ASSIGNMENT_FILE], []);
    }

    /**
     * @throws Throwable
     */
    public function testNoObjectTypedPropertiesSucceeds(): void
    {
        $this->analyse([self::VALID_NO_OBJECT_TYPES_FILE], []);
    }

    /**
     * @throws Throwable
     */
    public function testTypedPropertyMismatchFails(): void
    {
        $this->analyse([
            self::INVALID_FOO_DATA_FILE,
            self::FIXTURES_DIR . '/Invalid/InvalidTypedProperty.php',
        ], [
            [self::FOO_DATA_MISMATCH_MESSAGE, 9],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testPromotedPropertyMismatchFails(): void
    {
        $this->analyse([
            self::INVALID_FOO_DATA_FILE,
            self::FIXTURES_DIR . '/Invalid/InvalidPromotedProperty.php',
        ], [
            [self::FOO_DATA_MISMATCH_MESSAGE, 10],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testAssignmentMismatchFails(): void
    {
        $this->analyse([
            self::INVALID_FOO_DATA_FILE,
            self::FIXTURES_DIR . '/Invalid/InvalidAssignment.php',
        ], [
            [self::ASSIGNMENT_MISMATCH_MESSAGE, 9],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testCloneAssignmentMismatchFails(): void
    {
        $this->analyse([
            self::INVALID_FOO_DATA_FILE,
            self::FIXTURES_DIR . '/Invalid/InvalidCloneAssignment.php',
        ], [
            [self::ASSIGNMENT_MISMATCH_MESSAGE, 9],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testUnionAssignmentMismatchFails(): void
    {
        $this->analyse([
            self::INVALID_FOO_DATA_FILE,
            self::INVALID_BAR_DATA_FILE,
            self::FIXTURES_DIR . '/Invalid/InvalidUnionAssignment.php',
        ], [
            [self::UNION_MISMATCH_MESSAGE, 9],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testInterfaceBareNameSucceedsWhenCheckDisabledByDefault(): void
    {
        $this->analyse([
            self::INVALID_CHANNEL_INTERFACE_FILE,
            self::INVALID_INTERFACE_BARE_NAME_FILE,
        ], []);
    }

    /**
     * @throws Throwable
     */
    public function testInterfaceBareNameFailsWhenCheckEnabled(): void
    {
        $scope = $this->createScopeStubForResolvedTypeName([
            'ChannelInterface' => RuntimeChannelInterface::class,
        ]);
        $rule      = $this->createRuleWithInterfaceBareNameCheckEnabled();
        $errorList = $rule->processNode(
            $this->createPropertyNodeWithTypeNode(
                new Name('ChannelInterface'),
                'channel',
                9,
            ),
            $scope,
        );

        self::assertCount(1, $errorList);
        self::assertSame(self::INTERFACE_BARE_NAME_MESSAGE, $errorList[0]->getMessage());

        if (($errorList[0] instanceof IdentifierRuleError) === false) {
            self::fail('Expected IdentifierRuleError implementation.');
        }

        self::assertSame(self::INTERFACE_BARE_NAME_IDENTIFIER, $errorList[0]->getIdentifier());

        if (($errorList[0] instanceof LineRuleError) === false) {
            self::fail('Expected LineRuleError implementation.');
        }

        self::assertSame(9, $errorList[0]->getLine());
    }

    /**
     * @throws Throwable
     */
    public function testDenyListedInterfaceOnConcreteTypeReportsOnlySuffixMismatchFails(): void
    {
        $this->denyList = new DenyList(
            classNameList: [
                DenyListedInterface::class,
            ],
        );

        $this->analyse([
            self::DENYLIST_INTERFACE_FILE,
            self::DENYLIST_CONCRETE_FILE,
            self::DENYLIST_FIXTURE_FILE,
        ], [
            [self::DENYLIST_MISMATCH_MESSAGE, 9],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testDenyListedInterfaceOnConcreteTypeWithInterfaceBareNameCheckEnabledReportsOnlySuffixMismatchFails(): void
    {
        $denyList = new DenyList(
            classNameList: [
                RuntimeDenyListedInterface::class,
            ],
        );
        $scope = $this->createScopeStubForResolvedTypeName([
            'DenyListedConcreteClass' => RuntimeDenyListedConcreteClass::class,
        ]);
        $rule      = $this->createRuleWithInterfaceBareNameCheckEnabled($denyList);
        $errorList = $rule->processNode(
            $this->createPropertyNodeWithTypeNode(
                new Name('DenyListedConcreteClass'),
                'denyListed',
                9,
            ),
            $scope,
        );

        self::assertCount(1, $errorList);
        self::assertSame(self::DENYLIST_MISMATCH_MESSAGE, $errorList[0]->getMessage());

        if (($errorList[0] instanceof IdentifierRuleError) === false) {
            self::fail('Expected IdentifierRuleError implementation.');
        }

        self::assertSame(self::TYPE_SUFFIX_MISMATCH_IDENTIFIER, $errorList[0]->getIdentifier());
    }

    /**
     * @throws Throwable
     */
    public function testNullableTypedPropertyMismatchFails(): void
    {
        $this->analyse([
            self::INVALID_FOO_DATA_FILE,
            self::INVALID_NULLABLE_FILE,
        ], [
            [self::FOO_DATA_MISMATCH_MESSAGE, 11],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testUnionTypedPropertyMismatchFails(): void
    {
        $this->analyse([
            self::INVALID_FOO_DATA_FILE,
            self::INVALID_BAR_DATA_FILE,
            self::INVALID_UNION_PROPERTY_FILE,
        ], [
            [self::UNION_PROPERTY_MISMATCH, 12],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testDnfUnionIntersectionTypedPropertyMismatchFails(): void
    {
        $this->analyse([
            self::VALID_CHANNEL_INTERFACE_FILE,
            self::VALID_FOO_DATA_FILE,
            self::VALID_BAR_DATA_FILE,
            self::VALID_DNF_TYPE_FILE,
        ], [
            [self::DNF_UNION_MISMATCH, 13],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testIntersectionTypedPropertyMismatchFails(): void
    {
        $this->analyse([
            self::INVALID_FOO_DATA_FILE,
            self::INVALID_CHANNEL_INTERFACE_FILE,
            self::INVALID_INTERSECTION_PROPERTY_FILE,
        ], [
            [self::INTERSECTION_PROPERTY_MISMATCH, 12],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testIntersectionPromotedPropertyMismatchFails(): void
    {
        $this->analyse([
            self::INVALID_FOO_DATA_FILE,
            self::INVALID_CHANNEL_INTERFACE_FILE,
            self::INVALID_INTERSECTION_PROMOTED_PROPERTY_FILE,
        ], [
            [self::INTERSECTION_PROPERTY_MISMATCH, 13],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testIntersectionInlineVarAnnotationNarrowsAssignmentTypeSucceeds(): void
    {
        $this->analyse([self::VALID_INTERSECTION_INLINE_VAR_ASSIGNMENT_FILE], []);
    }

    /**
     * @throws Throwable
     */
    public function testIntersectionTypedPropertyDocblockNarrowsTypeSucceeds(): void
    {
        $this->analyse([self::VALID_INTERSECTION_TYPED_PROPERTY_DOCBLOCK_FILE], []);
    }

    /**
     * @throws Throwable
     */
    public function testIntersectionPromotedPropertyDocblockNarrowsTypeSucceeds(): void
    {
        $this->analyse([self::VALID_INTERSECTION_PROMOTED_PROPERTY_DOCBLOCK_FILE], []);
    }

    /**
     * @throws Throwable
     */
    public function testDuplicateInterfaceBaseNameSucceedsWhenCheckDisabledByDefault(): void
    {
        $this->analyse([
            self::VALID_DUPLICATE_A_FILE,
            self::VALID_DUPLICATE_B_FILE,
            self::INVALID_DUPLICATE_INTERFACE_BASE_NAME_FILE,
        ], []);
    }

    /**
     * @throws Throwable
     */
    public function testDuplicateInterfaceBaseNameFailsWhenCheckEnabled(): void
    {
        $scope = $this->createScopeStubForResolvedTypeName([
            'AChannelInterface' => RuntimeAChannelInterface::class,
            'BChannelInterface' => RuntimeBChannelInterface::class,
        ]);
        $rule      = $this->createRuleWithInterfaceBareNameCheckEnabled();
        $errorList = $rule->processNode(
            $this->createPropertyNodeWithTypeNode(
                new ParserUnionType([
                    new Name('AChannelInterface'),
                    new Name('BChannelInterface'),
                ]),
                'channel',
                12,
            ),
            $scope,
        );

        self::assertCount(1, $errorList);
        self::assertSame(self::INTERFACE_BARE_NAME_MESSAGE, $errorList[0]->getMessage());

        if (($errorList[0] instanceof IdentifierRuleError) === false) {
            self::fail('Expected IdentifierRuleError implementation.');
        }

        self::assertSame(self::INTERFACE_BARE_NAME_IDENTIFIER, $errorList[0]->getIdentifier());
    }

    /**
     * @throws Throwable
     */
    public function testGlobalClassTypedPropertyMismatchFails(): void
    {
        $this->analyse([
            self::VALID_GLOBAL_FOO_DATA_FILE,
            self::INVALID_GLOBAL_TYPE_FILE,
        ], [
            [self::GLOBAL_FOO_DATA_MISMATCH, 9],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testConcreteClassEndingWithInterfaceNameSucceedsWhenCheckEnabled(): void
    {
        $scope = $this->createScopeStubForResolvedTypeName([
            'FooInterface' => RuntimeFooInterface::class,
        ]);
        $rule      = $this->createRuleWithInterfaceBareNameCheckEnabled();
        $errorList = $rule->processNode(
            $this->createPropertyNodeWithTypeNode(
                new Name('FooInterface'),
                'foo',
                9,
            ),
            $scope,
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testScenarioBConcreteClassImplementingInterfaceWithBareNameSucceedsWhenCheckEnabled(): void
    {
        $scope = $this->createScopeStubForResolvedTypeName([
            'ReadChannel' => RuntimeReadChannel::class,
        ]);
        $rule      = $this->createRuleWithInterfaceBareNameCheckEnabled();
        $errorList = $rule->processNode(
            $this->createPropertyNodeWithTypeNode(
                new Name('ReadChannel'),
                'channel',
                30,
            ),
            $scope,
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testScenarioEUnionContainingConcreteAndUnrelatedInterfaceWithBareNameSucceedsWhenCheckEnabled(): void
    {
        $scope = $this->createScopeStubForResolvedTypeName([
            'ReadChannel'        => RuntimeReadChannel::class,
            'TransportInterface' => RuntimeTransportInterface::class,
        ]);
        $rule      = $this->createRuleWithInterfaceBareNameCheckEnabled();
        $errorList = $rule->processNode(
            $this->createPropertyNodeWithTypeNode(
                new ParserUnionType([
                    new Name('ReadChannel'),
                    new Name('TransportInterface'),
                ]),
                'channel',
                32,
            ),
            $scope,
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testScenarioFAbstractClassImplementingInterfaceWithBareNameSucceedsWhenCheckEnabled(): void
    {
        $scope = $this->createScopeStubForResolvedTypeName([
            'AbstractChannel' => RuntimeAbstractChannel::class,
        ]);
        $rule      = $this->createRuleWithInterfaceBareNameCheckEnabled();
        $errorList = $rule->processNode(
            $this->createPropertyNodeWithTypeNode(
                new Name('AbstractChannel'),
                'channel',
                34,
            ),
            $scope,
        );

        self::assertSame([], $errorList);
    }

    /**
     * @throws Throwable
     */
    public function testScenarioGConcreteClassWithMultipleInterfacesAndMatchingBareNamesSucceedsWhenCheckEnabled(): void
    {
        $scope = $this->createScopeStubForResolvedTypeName([
            'MyService' => RuntimeMyService::class,
        ]);
        $rule = $this->createRuleWithInterfaceBareNameCheckEnabled();

        $channelErrorList = $rule->processNode(
            $this->createPropertyNodeWithTypeNode(
                new Name('MyService'),
                'channel',
                36,
            ),
            $scope,
        );
        $loggableErrorList = $rule->processNode(
            $this->createPropertyNodeWithTypeNode(
                new Name('MyService'),
                'loggable',
                37,
            ),
            $scope,
        );

        self::assertSame([], $channelErrorList);
        self::assertSame([], $loggableErrorList);
    }

    /**
     * @param array<string, string> $resolvedTypeNameMap
     */
    private function createScopeStubForResolvedTypeName(array $resolvedTypeNameMap): Scope&NodeCallbackInvoker
    {
        $this->resolvedTypeNameMap = $resolvedTypeNameMap;

        /** @var NodeCallbackInvoker&Scope&Stub $scope */
        $scope = self::createStubForIntersectionOfInterfaces([Scope::class, NodeCallbackInvoker::class]);
        $scope->method('resolveName')
            ->willReturnCallback($this->resolveTypeNameForScope(...));

        return $scope;
    }

    private function resolveTypeNameForScope(Name $name): string
    {
        $shortTypeName = $name->toString();

        return $this->resolvedTypeNameMap[$shortTypeName] ?? $shortTypeName;
    }

    private function createRuleWithInterfaceBareNameCheckEnabled(?DenyList $denyList = null): TypeSuffixMismatchRule
    {
        $resolvedDenyList = $denyList;

        if ($resolvedDenyList === null) {
            $resolvedDenyList = new DenyList();
        }

        return new TypeSuffixMismatchRule(
            typeCandidateResolver: new TypeCandidateResolver(
                denyList: $resolvedDenyList,
            ),
            enableInterfaceBareNameCheck: true,
        );
    }

    private function createPropertyNodeWithTypeNode(
        ComplexType|Identifier|Name $typeNode,
        string $propertyName,
        int $line,
    ): Property {
        return new Property(
            flags: Modifiers::PRIVATE,
            props: [
                new PropertyItem(
                    name: $propertyName,
                    default: null,
                    attributes: ['startLine' => $line],
                ),
            ],
            attributes: ['startLine' => $line],
            type: $typeNode,
        );
    }

    private function skipCoverageUnstablePromotedPropertyParamDocblockFixture(): void
    {
        if ($this->isCoverageModeEnabled === true) {
            self::markTestSkipped(
                'Coverage-mode instability on Windows for RuleTestCase fixture with promoted property method-level @param.',
            );
        }
    }
}
