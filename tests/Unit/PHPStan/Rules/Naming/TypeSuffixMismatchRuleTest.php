<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\TypeSuffixMismatchRule;
use SquidIT\PhpCodingStandards\PHPStan\Support\DenyList;
use SquidIT\PhpCodingStandards\PHPStan\Support\TypeCandidateResolver;
use Throwable;
use TypeSuffixMismatchFixtures\Invalid\DenyListRegression\DenyListedInterface;

/**
 * @extends RuleTestCase<TypeSuffixMismatchRule>
 */
final class TypeSuffixMismatchRuleTest extends RuleTestCase
{
    private const string FIXTURES_DIR                   = __DIR__ . '/Fixtures/TypeSuffixMismatch';
    private const string INVALID_FOO_DATA_FILE          = self::FIXTURES_DIR . '/Invalid/FooData.php';
    private const string INVALID_BAR_DATA_FILE          = self::FIXTURES_DIR . '/Invalid/BarData.php';
    private const string INVALID_CHANNEL_INTERFACE_FILE = self::FIXTURES_DIR . '/Invalid/ChannelInterface.php';
    private const string VALID_CHANNEL_INTERFACE_FILE   = self::FIXTURES_DIR . '/Valid/ChannelInterface.php';
    private const string VALID_FOO_DATA_FILE            = self::FIXTURES_DIR . '/Valid/FooData.php';
    private const string VALID_BAR_DATA_FILE            = self::FIXTURES_DIR . '/Valid/BarData.php';
    private const string VALID_DYNAMIC_ASSIGNMENT_FILE  = self::FIXTURES_DIR . '/Valid/EdgeCases/AssignmentWithDynamicVariableName.php';
    private const string VALID_SCALAR_ASSIGNMENT_FILE   = self::FIXTURES_DIR . '/Valid/EdgeCases/ScalarAssignmentNoObjectType.php';
    private const string VALID_NO_OBJECT_TYPES_FILE     = self::FIXTURES_DIR . '/Valid/EdgeCases/NoObjectTypedProperties.php';
    private const string VALID_DNF_TYPE_FILE            = self::FIXTURES_DIR . '/Valid/EdgeCases/DnfTypedPropertyIgnored.php';
    private const string VALID_DUPLICATE_A_FILE         = self::FIXTURES_DIR . '/Valid/EdgeCases/DuplicateBaseName/A/ChannelInterface.php';
    private const string VALID_DUPLICATE_B_FILE         = self::FIXTURES_DIR . '/Valid/EdgeCases/DuplicateBaseName/B/ChannelInterface.php';
    private const string VALID_GLOBAL_FOO_DATA_FILE     = self::FIXTURES_DIR . '/Valid/EdgeCases/GlobalFooData.php';
    private const string INVALID_NULLABLE_FILE          = self::FIXTURES_DIR . '/Invalid/EdgeCases/NullableTypedPropertyMismatch.php';
    private const string INVALID_UNION_PROPERTY_FILE    = self::FIXTURES_DIR . '/Invalid/EdgeCases/UnionTypedPropertyMismatch.php';
    private const string INVALID_DUPLICATE_NOTICE_FILE  = self::FIXTURES_DIR . '/Invalid/EdgeCases/DuplicateInterfaceBaseNameNotice.php';
    private const string INVALID_GLOBAL_TYPE_FILE       = self::FIXTURES_DIR . '/Invalid/EdgeCases/GlobalClassTypedPropertyMismatch.php';
    private const string DENYLIST_INTERFACE_FILE        = self::FIXTURES_DIR . '/Invalid/DenyListRegression/DenyListedInterface.php';
    private const string DENYLIST_CONCRETE_FILE         = self::FIXTURES_DIR . '/Invalid/DenyListRegression/DenyListedConcreteClass.php';
    private const string DENYLIST_FIXTURE_FILE          = self::FIXTURES_DIR . '/Invalid/DenyListRegression/DenyListDoubleErrorCandidate.php';
    private const string FOO_DATA_MISMATCH_MESSAGE      = 'Name "$service" does not match inferred type "FooData". Allowed base names: fooData. Use one of these names directly or a contextual prefix ending with: FooData.';
    private const string ASSIGNMENT_MISMATCH_MESSAGE    = 'Name "$item" does not match inferred type "FooData". Allowed base names: fooData. Use one of these names directly or a contextual prefix ending with: FooData.';
    private const string UNION_MISMATCH_MESSAGE         = 'Name "$item" does not match inferred type "BarData|FooData". Allowed base names: barData, fooData. Use one of these names directly or a contextual prefix ending with: BarData, FooData.';
    private const string UNION_PROPERTY_MISMATCH        = 'Name "$service" does not match inferred type "BarData|FooData". Allowed base names: barData, fooData. Use one of these names directly or a contextual prefix ending with: BarData, FooData.';
    private const string DNF_UNION_MISMATCH             = 'Name "$service" does not match inferred type "BarData|ChannelInterface|FooData". Allowed base names: barData, channel, fooData. Use one of these names directly or a contextual prefix ending with: BarData, Channel, FooData.';
    private const string INTERFACE_NOTICE_MESSAGE       = 'Interface-typed name "$channel" uses the bare interface base name "channel" (inferred interface type: ChannelInterface). Prefer a contextual prefix like "$readChannel".';
    private const string DENYLIST_MISMATCH_MESSAGE      = 'Name "$denyListed" does not match inferred type "DenyListedConcreteClass". Allowed base names: denyListedConcreteClass. Use one of these names directly or a contextual prefix ending with: DenyListedConcreteClass.';
    private const string GLOBAL_FOO_DATA_MISMATCH       = 'Name "$service" does not match inferred type "GlobalFooData". Allowed base names: globalFooData. Use one of these names directly or a contextual prefix ending with: GlobalFooData.';

    private DenyList $denyList;

    protected function setUp(): void
    {
        parent::setUp();

        $this->denyList = new DenyList();
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
    public function testInterfaceBareNameNoticeFails(): void
    {
        $this->analyse([
            self::INVALID_CHANNEL_INTERFACE_FILE,
            self::FIXTURES_DIR . '/Invalid/InterfaceBareNameNotice.php',
        ], [
            [self::INTERFACE_NOTICE_MESSAGE, 9],
        ]);
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
    public function testDuplicateInterfaceBaseNameNoticeFails(): void
    {
        $this->analyse([
            self::VALID_DUPLICATE_A_FILE,
            self::VALID_DUPLICATE_B_FILE,
            self::INVALID_DUPLICATE_NOTICE_FILE,
        ], [
            [self::INTERFACE_NOTICE_MESSAGE, 12],
        ]);
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
}
