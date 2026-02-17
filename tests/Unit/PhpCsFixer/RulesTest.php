<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PhpCsFixer;

use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PhpCsFixer\Rules;

final class RulesTest extends TestCase
{
    public function testGetRulesWithoutOverridesReturnsDefaultRulesSucceeds(): void
    {
        $rules = Rules::getRules();

        self::assertTrue($rules['@Symfony']);
        self::assertFalse($rules['global_namespace_import']);
        self::assertSame(
            [
                'default'   => 'align_single_space_minimal',
                'operators' => [
                    '===' => 'single_space',
                    '??'  => 'single_space',
                ],
            ],
            $rules['binary_operator_spaces'],
        );
    }

    public function testGetRulesWithOverridesReturnsMergedRulesSucceeds(): void
    {
        $rules = Rules::getRules([
            '@Symfony'               => false,
            'custom_rule'            => true,
            'binary_operator_spaces' => [
                'default' => 'single_space',
            ],
        ]);

        self::assertFalse($rules['@Symfony']);
        self::assertTrue($rules['custom_rule']);
        self::assertSame(
            [
                'default' => 'single_space',
            ],
            $rules['binary_operator_spaces'],
        );
    }

    public function testGetRulesWithOverridesDoesNotMutateDefaultRulesSucceeds(): void
    {
        Rules::getRules(['@Symfony' => false]);
        $rules = Rules::getRules();

        self::assertTrue($rules['@Symfony']);
    }
}
