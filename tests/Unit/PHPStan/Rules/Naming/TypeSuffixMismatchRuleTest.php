<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\TypeSuffixMismatchRule;
use SquidIT\PhpCodingStandards\PHPStan\Support\DenyList;
use SquidIT\PhpCodingStandards\PHPStan\Support\TypeCandidateResolver;
use Throwable;

/**
 * @extends RuleTestCase<TypeSuffixMismatchRule>
 */
final class TypeSuffixMismatchRuleTest extends RuleTestCase
{
    private const string FIXTURES_DIR                = __DIR__ . '/../Fixtures/Naming/TypeSuffixMismatch';
    private const string DENYLIST_FIXTURE_FILE       = self::FIXTURES_DIR . '/Invalid/DenyListRegression/DenyListDoubleErrorCandidate.php';
    private const string FOO_DATA_MISMATCH_MESSAGE   = 'Name "$service" does not match inferred type "FooData". Allowed base names: fooData. Use one of these names directly or a contextual prefix ending with: FooData.';
    private const string ASSIGNMENT_MISMATCH_MESSAGE = 'Name "$item" does not match inferred type "FooData". Allowed base names: fooData. Use one of these names directly or a contextual prefix ending with: FooData.';
    private const string UNION_MISMATCH_MESSAGE      = 'Name "$item" does not match inferred type "BarData|FooData". Allowed base names: barData, fooData. Use one of these names directly or a contextual prefix ending with: BarData, FooData.';
    private const string INTERFACE_NOTICE_MESSAGE    = 'Interface-typed name "$channel" uses the bare interface base name "channel" (inferred interface type: ChannelInterface). Prefer a contextual prefix like "$readChannel".';
    private const string DENYLIST_MISMATCH_MESSAGE   = 'Name "$denyListed" does not match inferred type "DenyListedConcreteClass". Allowed base names: denyListedConcreteClass. Use one of these names directly or a contextual prefix ending with: DenyListedConcreteClass.';

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
     * @throws Throwable
     */
    public function testValidScenariosSucceeds(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Valid/ValidScenarios.php'], []);
    }

    /**
     * @throws Throwable
     */
    public function testTypedPropertyMismatchFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/InvalidTypedProperty.php'], [
            [self::FOO_DATA_MISMATCH_MESSAGE, 11],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testPromotedPropertyMismatchFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/InvalidPromotedProperty.php'], [
            [self::FOO_DATA_MISMATCH_MESSAGE, 12],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testAssignmentMismatchFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/InvalidAssignment.php'], [
            [self::ASSIGNMENT_MISMATCH_MESSAGE, 11],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testCloneAssignmentMismatchFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/InvalidCloneAssignment.php'], [
            [self::ASSIGNMENT_MISMATCH_MESSAGE, 11],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testUnionAssignmentMismatchFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/InvalidUnionAssignment.php'], [
            [self::UNION_MISMATCH_MESSAGE, 13],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testInterfaceBareNameNoticeFails(): void
    {
        $this->analyse([self::FIXTURES_DIR . '/Invalid/InterfaceBareNameNotice.php'], [
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
                'TypeSuffixMismatchFixtures\Invalid\DenyListRegression\DenyListedInterface',
            ],
        );

        $this->analyse([self::DENYLIST_FIXTURE_FILE], [
            [self::DENYLIST_MISMATCH_MESSAGE, 9],
        ]);
    }
}
