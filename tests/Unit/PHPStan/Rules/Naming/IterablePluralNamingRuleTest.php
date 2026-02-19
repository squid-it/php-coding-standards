<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming;

use PhpParser\Node\Stmt\Expression;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\IterablePluralNamingRule;
use Throwable;

/**
 * @extends RuleTestCase<IterablePluralNamingRule>
 */
final class IterablePluralNamingRuleTest extends RuleTestCase
{
    private const string FIXTURES_DIR                  = __DIR__ . '/Fixtures/IterablePluralNaming';
    private const string VALID_NODE_FILE               = self::FIXTURES_DIR . '/Valid/Node.php';
    private const string VALID_INLINE_VAR_FILE         = self::FIXTURES_DIR . '/Valid/EdgeCases/InlineVarAnnotationNarrowsIterableAssignmentType.php';
    private const string VALID_INLINE_UNNAMED_VAR_FILE = self::FIXTURES_DIR . '/Valid/EdgeCases/InlineUnnamedVarAnnotationNarrowsIterableAssignmentType.php';
    private const string INLINE_VAR_CONTAINER_INTERFACE_FILE = self::FIXTURES_DIR . '/Valid/EdgeCases/InlineVarAnnotation/ContainerInterface.php';
    private const string INLINE_VAR_CONTAINER_MASON_INTERFACE_FILE = self::FIXTURES_DIR . '/Valid/EdgeCases/InlineVarAnnotation/ContainerMasonInterface.php';
    private const string INVALID_NODE_FILE             = self::FIXTURES_DIR . '/Invalid/Node.php';
    private const string PLURAL_MISMATCH_ERROR_MESSAGE = 'Iterable name "$itemList" does not match inferred iterable element type "Node". Allowed base names: node. Use plural form or collection suffixes: List, Collection, Lookup, ById, ByKey.';
    private const string MAP_FORBIDDEN_ERROR_MESSAGE   = 'Iterable name "$nodeMap" contains forbidden segment "Map". Use "List", "Collection", "Lookup", "ById", or "ByKey" naming instead.';
    private const string MAP_MISMATCH_ERROR_MESSAGE    = 'Iterable name "$nodeMap" does not match inferred iterable element type "Node". Allowed base names: node. Use plural form or collection suffixes: List, Collection, Lookup, ById, ByKey.';

    private bool $isCoverageModeEnabled;

    protected function setUp(): void
    {
        parent::setUp();

        $xdebugMode = getenv('XDEBUG_MODE');

        $this->isCoverageModeEnabled = $xdebugMode !== false && str_contains((string) $xdebugMode, 'coverage');
    }

    protected function getRule(): Rule
    {
        return new IterablePluralNamingRule();
    }

    /**
     * @return array<int, string>
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/IterablePluralNamingRuleTest.neon'];
    }

    /**
     * @throws Throwable
     */
    public function testValidIterableNamesSucceeds(): void
    {
        $this->analyse([
            self::VALID_NODE_FILE,
            self::FIXTURES_DIR . '/Valid/ValidScenarios.php',
        ], []);
    }

    /**
     * @throws Throwable
     */
    public function testNamesContainingMapSubstringWithoutMapSegmentSucceeds(): void
    {
        $this->analyse([
            self::VALID_NODE_FILE,
            self::FIXTURES_DIR . '/Valid/MapSubstringWithoutMapSegment.php',
        ], []);
    }

    /**
     * @throws Throwable
     */
    public function testInlineVarAnnotationNarrowsIterableAssignmentTypeSucceeds(): void
    {
        $this->skipCoverageUnstableInlineVarIterableFixture();
        $this->analyse([
            self::INLINE_VAR_CONTAINER_INTERFACE_FILE,
            self::INLINE_VAR_CONTAINER_MASON_INTERFACE_FILE,
            self::VALID_INLINE_VAR_FILE,
        ], []);
    }

    /**
     * @throws Throwable
     */
    public function testInlineUnnamedVarAnnotationNarrowsIterableAssignmentTypeSucceeds(): void
    {
        $this->skipCoverageUnstableInlineVarIterableFixture();
        $this->analyse([
            self::INLINE_VAR_CONTAINER_INTERFACE_FILE,
            self::INLINE_VAR_CONTAINER_MASON_INTERFACE_FILE,
            self::VALID_INLINE_UNNAMED_VAR_FILE,
        ], []);
    }

    /**
     * @throws Throwable
     */
    public function testGetNodeTypeReturnsExpressionClassSucceeds(): void
    {
        self::assertSame(Expression::class, (new IterablePluralNamingRule())->getNodeType());
    }

    /**
     * @throws Throwable
     */
    public function testInvalidPluralNameFails(): void
    {
        $this->analyse([
            self::INVALID_NODE_FILE,
            self::FIXTURES_DIR . '/Invalid/InvalidPluralName.php',
        ], [
            [self::PLURAL_MISMATCH_ERROR_MESSAGE, 11],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testMapNameFails(): void
    {
        $this->analyse([
            self::INVALID_NODE_FILE,
            self::FIXTURES_DIR . '/Invalid/InvalidMapName.php',
        ], [
            [self::MAP_FORBIDDEN_ERROR_MESSAGE, 11],
            [self::MAP_MISMATCH_ERROR_MESSAGE, 11],
        ]);
    }

    private function skipCoverageUnstableInlineVarIterableFixture(): void
    {
        if ($this->isCoverageModeEnabled === true) {
            self::markTestSkipped(
                'Coverage-mode instability on Windows for RuleTestCase fixture with inline iterable @var narrowing.',
            );
        }
    }
}
