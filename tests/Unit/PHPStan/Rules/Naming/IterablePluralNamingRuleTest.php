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
    private const string FIXTURES_DIR                              = __DIR__ . '/Fixtures/IterablePluralNaming';
    private const string VALID_NODE_FILE                           = self::FIXTURES_DIR . '/Valid/Node.php';
    private const string VALID_INLINE_VAR_FILE                     = self::FIXTURES_DIR . '/Valid/EdgeCases/InlineVarAnnotationNarrowsIterableAssignmentType.php';
    private const string VALID_INLINE_UNNAMED_VAR_FILE             = self::FIXTURES_DIR . '/Valid/EdgeCases/InlineUnnamedVarAnnotationNarrowsIterableAssignmentType.php';
    private const string INLINE_VAR_CONTAINER_INTERFACE_FILE       = self::FIXTURES_DIR . '/Valid/EdgeCases/InlineVarAnnotation/ContainerInterface.php';
    private const string INLINE_VAR_CONTAINER_MASON_INTERFACE_FILE = self::FIXTURES_DIR . '/Valid/EdgeCases/InlineVarAnnotation/ContainerMasonInterface.php';
    private const string INVALID_NODE_FILE                         = self::FIXTURES_DIR . '/Invalid/Node.php';
    private const string MISSING_LIST_SUFFIX_ERROR_MESSAGE         = 'Iterable name "$nodes" does not match inferred iterable element type "Node". Allowed base names: node. Use one of these names directly or a contextual prefix ending with: nodeList.';
    private const string LEGACY_BY_ID_SUFFIX_ERROR_MESSAGE         = 'Iterable name "$nodeById" does not match inferred iterable element type "Node". Allowed base names: node. Use one of these names directly or a contextual prefix ending with: nodeList.';
    private const string LEGACY_BY_KEY_SUFFIX_ERROR_MESSAGE        = 'Iterable name "$nodeByKey" does not match inferred iterable element type "Node". Allowed base names: node. Use one of these names directly or a contextual prefix ending with: nodeList.';
    private const string LEGACY_LOOKUP_SUFFIX_ERROR_MESSAGE        = 'Iterable name "$nodeLookup" does not match inferred iterable element type "Node". Allowed base names: node. Use one of these names directly or a contextual prefix ending with: nodeList.';
    private const string LEGACY_COLLECTION_SUFFIX_ERROR_MESSAGE    = 'Iterable name "$nodeCollection" does not match inferred iterable element type "Node". Allowed base names: node. Use one of these names directly or a contextual prefix ending with: nodeList.';
    private const string MAP_FORBIDDEN_ERROR_MESSAGE               = 'Iterable name "$nodeMap" contains forbidden segment "Map". Use "*List" naming instead.';
    private const string MAP_MISMATCH_ERROR_MESSAGE                = 'Iterable name "$nodeMap" does not match inferred iterable element type "Node". Allowed base names: node. Use one of these names directly or a contextual prefix ending with: nodeList.';

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
    public function testMissingListSuffixFails(): void
    {
        $this->analyse([
            self::INVALID_NODE_FILE,
            self::FIXTURES_DIR . '/Invalid/InvalidPluralName.php',
        ], [
            [self::MISSING_LIST_SUFFIX_ERROR_MESSAGE, 11],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testLegacyCollectionSuffixesFail(): void
    {
        $this->analyse([
            self::INVALID_NODE_FILE,
            self::FIXTURES_DIR . '/Invalid/InvalidLegacyCollectionSuffixes.php',
        ], [
            [self::LEGACY_BY_ID_SUFFIX_ERROR_MESSAGE, 11],
            [self::LEGACY_BY_KEY_SUFFIX_ERROR_MESSAGE, 12],
            [self::LEGACY_LOOKUP_SUFFIX_ERROR_MESSAGE, 13],
            [self::LEGACY_COLLECTION_SUFFIX_ERROR_MESSAGE, 14],
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
