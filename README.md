# PHP Coding Standards - PHP-CS-Fixer Rules
Default coding standard rules for PHP-CS-Fixer.


### Usage

Below you can find an example file named `.php-cs-fixer.dist.php` which should be placed inside your project's root directory.

```php
<?php 

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use SquidIT\PhpCodingStandards\PhpCsFixer\Rules;

$finder = Finder::create()
    ->in(__DIR__);

$overrides = [
    'modernize_types_casting' => false,
];

return (new Config())
    ->setFinder($finder)
    ->setCacheFile('.php-cs-fixer.cache')
    ->setRiskyAllowed(true)    
    ->setRules(Rules::getRules($overrides));
```

### Manual Triggering
Run following command in your project directory, that will run fixer for every `.php` file.
```bash
vendor/bin/php-cs-fixer fix
```

---

### PHPStan Rules

This library also provides custom PHPStan rules that can be included in your project.

To use them, add the following to your project's `phpstan.neon`:

```neon
rules:
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Architecture\SingleClassPerFileRule
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Restrictions\DisallowAnonymousFunctionRule
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Restrictions\DisallowLogicalNotRule
```

Experimental autoreview rules are configured separately in `phpstan-autoreview.neon`.

#### Experimental Support Utilities (Maintainers)

- `TypeCandidateResolver` resolves naming candidates from inferred PHPStan types.
- `VariableNameMatcher` validates variable naming against normalized base candidates.
- `DenyList` is an internal tuning object used by resolver/matcher flows to suppress specific class or candidate names when reducing false positives.
- This support layer is internal for experimental autoreview rules and not part of the stable public API.

#### RuleTestCase Fixtures (Maintainers)

- For `PHPStan\Testing\RuleTestCase` fixtures in `tests/Unit/PHPStan/Rules/<Module>/Fixtures`, do not use autoloaded `SquidIT\Tests\...` namespaces.
- Use isolated fixture namespaces (for example `TypeSuffixMismatchFixtures\...`) so fixture symbols are only loaded through fixture analysis.
- If a fixture file references other fixture classes/interfaces, add a rule-specific `.neon` file with `parameters.scanFiles` for those dependency fixture files.
- Register that `.neon` from the test class with `public static function getAdditionalConfigFiles(): array` to avoid `ReflectionProvider class not found` misconfiguration errors.
- Avoid manual `require_once` of fixture files in rule tests unless there is no alternative.
- Keep fixture-related `excludePaths` and `ignoreErrors` narrow and scoped to the exact fixture directory/identifier.
- This prevents coverage instability where `composer test:unit:coverage` can crash with Windows exit code `-1073741819` after report generation.

#### Available Rules

| Rule | Identifier | Description |
|------|------------|-------------|
| `SingleClassPerFileRule` | `squidit.architecture.singleClassPerFile` | Enforces that each PHP file contains only one class-like declaration (class, interface, trait, or enum). Anonymous classes are allowed. |
| `DisallowAnonymousFunctionRule` | `squidit.restrictions.disallowAnonymousFunction` | Disallows anonymous functions (closures) and arrow functions. Use an invokable class with an `__invoke()` method instead. |
| `DisallowLogicalNotRule` | `squidit.restrictions.disallowLogicalNot` | Disallows the logical NOT operator (`!`). Use explicit comparisons instead (`=== true`, `=== false`, `!== null`). |

#### Ignoring a Rule

To ignore a specific rule for a file or line, use the PHPStan ignore syntax with the rule identifier:

```php
// @phpstan-ignore squidit.architecture.singleClassPerFile
```

Or in your `phpstan.neon`:

```neon
parameters:
    ignoreErrors:
        - identifier: squidit.architecture.singleClassPerFile
```
