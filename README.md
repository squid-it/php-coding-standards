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
