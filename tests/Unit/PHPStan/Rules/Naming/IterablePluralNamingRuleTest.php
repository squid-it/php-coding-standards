<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Naming;

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
    private const string INVALID_NODE_FILE             = self::FIXTURES_DIR . '/Invalid/Node.php';
    private const string PLURAL_MISMATCH_ERROR_MESSAGE = 'Iterable name "$itemList" does not match inferred iterable element type "Node". Allowed base names: node. Use plural form or collection suffixes: List, Collection, Lookup, ById, ByKey.';
    private const string MAP_FORBIDDEN_ERROR_MESSAGE   = 'Iterable name "$nodeMap" contains forbidden segment "Map". Use "List", "Collection", "Lookup", "ById", or "ByKey" naming instead.';
    private const string MAP_MISMATCH_ERROR_MESSAGE    = 'Iterable name "$nodeMap" does not match inferred iterable element type "Node". Allowed base names: node. Use plural form or collection suffixes: List, Collection, Lookup, ById, ByKey.';

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

    /**
     * @throws Throwable
     */
}
