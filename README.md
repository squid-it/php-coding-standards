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

This library provides two sets of custom PHPStan rules:

- **Stable rules** — enforced conventions with a low false-positive rate, ready for CI.
- **Experimental auto review rules** — naming and architecture checks that support automated code review. Opt-in only.

---

#### Stable Rules

Add the following to your project's `phpstan.neon`:

```neon
rules:
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Architecture\SingleClassPerFileRule
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Restrictions\DisallowAnonymousFunctionRule
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Restrictions\DisallowLogicalNotRule
```

#### Stable Rules Reference

| Rule                            | Identifier                                       | Description                                                                                                                             |
|---------------------------------|--------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------|
| `SingleClassPerFileRule`        | `squidit.architecture.singleClassPerFile`        | Enforces that each PHP file contains only one class-like declaration (class, interface, trait, or enum). Anonymous classes are allowed. |
| `DisallowAnonymousFunctionRule` | `squidit.restrictions.disallowAnonymousFunction` | Disallows anonymous functions (closures) and arrow functions. Use an invokable class with an `__invoke()` method instead.               |
| `DisallowLogicalNotRule`        | `squidit.restrictions.disallowLogicalNot`        | Disallows the logical NOT operator (`!`). Use explicit comparisons instead (`=== true`, `=== false`, `!== null`).                       |

---

#### Experimental Auto review Rules

These rules are optional and must be explicitly opted into. They enforce naming conventions and architectural boundaries that support automated code review.

Copy-paste template for a dedicated `phpstan-autoreview.neon`:

```neon
parameters:
    level: 9
    paths:
        - src
        - tests
    treatPhpDocTypesAsCertain: false
    tmpDir: var/cache/phpstan-autoreview

services:
	-
		class: SquidIT\PhpCodingStandards\PHPStan\Support\NameNormalizer
	-
		class: SquidIT\PhpCodingStandards\PHPStan\Support\DenyList
	-
		class: SquidIT\PhpCodingStandards\PHPStan\Support\TypeCandidateResolver
	-
		class: SquidIT\PhpCodingStandards\PHPStan\Support\VariableNameMatcher
	-
		class: SquidIT\PhpCodingStandards\PHPStan\Support\TypeMessageDescriber
	-
		class: SquidIT\PhpCodingStandards\PHPStan\Support\Pluralizer
	-
		class: SquidIT\PhpCodingStandards\PHPStan\Support\Singularizer
	-
		class: SquidIT\PhpCodingStandards\PHPStan\Support\VoDtoClassifier
	-
		class: SquidIT\PhpCodingStandards\PHPStan\Support\ContainingClassResolver
	-
		class: SquidIT\PhpCodingStandards\PHPStan\Support\ComposerDevAutoloadPathMatcher
	-
		class: \SquidIT\PhpCodingStandards\PHPStan\Support\PhpDocTypeResolver

rules:
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\TypeSuffixMismatchRule
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\IterablePluralNamingRule
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\ForeachValueVariableNamingRule
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\LoggerContextKeyCamelCaseRule
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\EnumBackedValueCamelCaseRule
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Architecture\NoServiceInstantiationRule
```

#### Experimental Rules Reference

| Rule                             | Identifier(s)                                 | Description                                                                                                                                         |
|----------------------------------|-----------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------|
| `TypeSuffixMismatchRule`         | `squidit.naming.typeSuffixMismatch`           | Enforces that typed properties, promoted parameters, and local variable assignments are named consistently with their inferred type.                |
| `TypeSuffixMismatchRule`         | `squidit.naming.interfaceBareName`            | Optional check (disabled by default) that reports when a variable or property name uses an interface-derived base name without a contextual prefix. |
| `IterablePluralNamingRule`       | `squidit.naming.iterablePluralMismatch`       | Enforces plural or collection-style naming when an assignment holds an iterable of typed objects.                                                   |
| `IterablePluralNamingRule`       | `squidit.naming.mapForbidden`                 | Reports when an iterable assignment target contains the word segment `Map`.                                                                         |
| `ForeachValueVariableNamingRule` | `squidit.naming.foreachValueVarMismatch`      | Enforces that the foreach value variable is named after the singularized iterable variable or the inferred element type.                            |
| `LoggerContextKeyCamelCaseRule`  | `squidit.naming.loggerContextKeyCamelCase`    | Enforces camelCase for string-literal keys in the context array argument of `Psr\Log\LoggerInterface` method calls.                                 |
| `EnumBackedValueCamelCaseRule`   | `squidit.naming.enumBackedValueCamelCase`     | Enforces camelCase backed string values on string-backed enums, with an exception when the same literal is returned by a `to*()` method.            |
| `NoServiceInstantiationRule`     | `squidit.architecture.noServiceInstantiation` | Disallows `new` expressions for service classes outside of creator classes (`*Factory`, `*Builder`, `*Provider`), with configurable test/fixture skips. |

---

##### TypeSuffixMismatchRule

Checks typed properties, promoted constructor parameters, and local variable assignments. The variable or property name must reflect the inferred type — either as an exact base name match or with a contextual prefix.

By default, this rule enforces only `squidit.naming.typeSuffixMismatch`. The optional interface bare-name check (`squidit.naming.interfaceBareName`) is disabled by default.

Docblock narrowing support:
- Assignment statements: inline `@var` on the assignment statement is used.
- Typed properties: property-level `@var` is used.
- Promoted properties: parameter-level `@var` is used.
- Promoted properties: constructor docblock `@param` is used.

**Valid:**
```php
private FooService $fooService;
private FooService $activeFooService;
```

**Invalid:**
```php
private FooService $item;          // squidit.naming.typeSuffixMismatch
private FooServiceInterface $fooService;    // squidit.naming.interfaceBareName
                                            // (only when interface bare-name check is enabled)
```

The `interfaceBareName` identifier fires when the name matches an interface-derived base name exactly, without any contextual prefix. A prefix like `active` or `current` silences it.

**Enable the optional interface bare-name check:**

If you want this stricter behavior, remove `TypeSuffixMismatchRule` from `rules:` and register it via `services`:

```neon
services:
    -
        class: SquidIT\PhpCodingStandards\PHPStan\Rules\Naming\TypeSuffixMismatchRule
        tags:
            - phpstan.rules.rule
        arguments:
            $enableInterfaceBareNameCheck: true
```

Do not also list the rule under `rules:` when using `services:` wiring - PHPStan would register it twice.

---

##### IterablePluralNamingRule

Checks assignment targets where the inferred type is an iterable of typed objects. The variable or property name must use a plural or recognized collection-style form.

Docblock narrowing support:
- Assignment statements: inline `@var` on the assignment statement is used to resolve iterable value type candidates (named and unnamed `@var`).

**Allowed collection suffixes:** `List`, `Collection`, `Lookup`, `ById`, `ByKey`

**Valid:**
```php
$nodes = [$node];
$nodeList = [$node];
$activeNodeList = [$node];
$nodeById = ['id' => $node];
```

**Invalid:**
```php
$items = [$node];      // squidit.naming.iterablePluralMismatch
$nodeMap = [$node];    // squidit.naming.mapForbidden
```

The `mapForbidden` identifier fires whenever the name contains `Map` as a camelCase word segment (for example `nodeMap`, `nodeMapById`). Use `Lookup`, `ById`, or `ByKey` instead.

---

##### ForeachValueVariableNamingRule

Checks the value variable in a `foreach` statement. Naming candidates are resolved from two sources: the singularized iterable variable name and the inferred iterable element type. The value variable must match one of those candidates directly, or use it as a suffix with a contextual prefix.

**Valid** (given `$children` typed as `array<int, ChildNode>`):
```php
foreach ($children as $child) {}
foreach ($children as $childNode) {}
foreach ($children as $firstChildNode) {}
```

**Invalid:**
```php
foreach ($children as $item) {}    // squidit.naming.foreachValueVarMismatch
```

---

##### LoggerContextKeyCamelCaseRule

Checks string-literal keys in the context array argument of PSR logger calls. Applies only when the receiver type is compatible with `Psr\Log\LoggerInterface`. Dynamic keys and non-logger receivers are ignored.

Context argument positions:
- `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug` — second argument (index `1`)
- `log` — third argument (index `2`)

**Valid:**
```php
$logger->info('User saved', ['userId' => $id]);
$logger->log('info', 'User saved', ['requestId' => $id]);
```

**Invalid:**
```php
$logger->info('User saved', ['user_id' => $id]);    // squidit.naming.loggerContextKeyCamelCase
```

---

##### EnumBackedValueCamelCaseRule

Checks string-backed enum case values. Each backed value must be camelCase. A non-camelCase value is permitted only when the same literal string is returned from a `to*()` method on the same enum (for example `toDb()`, `toLabel()`). The `to*()` exception requires a word boundary — `toDb()` qualifies, `total()` does not.

**Valid:**
```php
enum Status: string
{
    case Active = 'active';
    case FooBar = 'fooBar';
}
```

**Valid with `to*()` exception:**
```php
enum Status: string
{
    case DbLegacy = 'db_legacy';

    public function toDb(): string
    {
        return match ($this) {
            self::DbLegacy => 'db_legacy',
        };
    }
}
```

**Invalid:**
```php
enum Status: string
{
    case DbLegacy = 'db_legacy';    // squidit.naming.enumBackedValueCamelCase
}
```

---

##### NoServiceInstantiationRule

Disallows `new` expressions for service classes in non-creator classes. A class is exempt when:

- The file path is inside a directory declared in the nearest `composer.json` `autoload-dev.psr-4` section (only when `$excludeComposerDevDirs: true`), or
- The containing class extends `PHPUnit\Framework\TestCase` (when `$skipPhpUnitTestCaseClasses: true`, default), or
- The containing class name ends with a creator suffix (`Factory`, `Builder`, or `Provider` by default), or
- The instantiated class is an internal/builtin PHP class (for example `DateTimeImmutable`), or
- The instantiated class passes the VO/DTO classifier gates.

**Valid:**
```php
// Inside a *Factory, *Builder, or *Provider class
class HttpClientFactory
{
    public function create(): HttpClient
    {
        return new HttpClient();    // allowed
    }
}

// VO/DTO and internal class instantiation anywhere
$dto   = new OrderDto($id, $amount);
$clock = new DateTimeImmutable();
```

**Invalid:**
```php
class ReportService
{
    public function generate(): void
    {
        $client = new HttpClient();    // squidit.architecture.noServiceInstantiation
    }
}
```

**VO/DTO classification:**

A class is classified as a VO/DTO when it passes both gates:

1. **Immutability gate** (one must be true):
   - The class is declared `readonly`, or
   - All declared and inherited non-static instance properties are `readonly`.

2. **Public API gate** (all must be true):
   - Public methods are limited to: `__construct`, `get*`, `is*`, `has*`, `toArray`, `jsonSerialize`, `__toString`, `equals`, `equalsTo`.
   - Any other declared public method disqualifies the class.
   - Prefixes `get`, `is`, `has` require a word boundary — `getOrder()` qualifies, `getter()` does not.

**Configuring creator suffixes and test/fixture skips:**

By default, the rule allows instantiation inside any class ending with `Factory`, `Builder`, or `Provider`, and it skips classes extending `PHPUnit\Framework\TestCase`.

If you want custom suffixes or skip behavior, remove `NoServiceInstantiationRule` from `rules:` and register it via `services`:

```neon
services:
    -
        class: SquidIT\PhpCodingStandards\PHPStan\Rules\Architecture\NoServiceInstantiationRule
        tags:
            - phpstan.rules.rule
        arguments:
            $allowedCreatorClassSuffixList:
                - Factory
                - Builder
                - Provider
                - Assembler
            $skipPhpUnitTestCaseClasses: true
            $excludeComposerDevDirs: false
```

Do not also list the rule under `rules:` when using `services:` wiring - PHPStan would register it twice. An empty list falls back to the defaults (`Factory`, `Builder`, `Provider`).
`$skipPhpUnitTestCaseClasses`:
- Default: `true`
- Set to `false` to enforce this rule inside PHPUnit test classes as well.

`$excludeComposerDevDirs`:
- Default: `false`
- Set to `true` to skip this rule for files under directories declared in `autoload-dev.psr-4` of the nearest `composer.json`.
- Example: `"SquidIT\\Tests\\PhpCodingStandards\\": "tests"` excludes `tests/*`.

---

#### Suppressing Rules

To suppress a specific rule on a single line:

```php
// @phpstan-ignore squidit.architecture.noServiceInstantiation
$client = new HttpClient();
```

To suppress globally in `phpstan.neon`:

```neon
parameters:
    ignoreErrors:
        - identifier: squidit.architecture.noServiceInstantiation
```

To suppress for a specific path:

```neon
parameters:
    ignoreErrors:
        -
            identifier: squidit.naming.typeSuffixMismatch
            path: src/Legacy/*
```

When the optional interface bare-name check is enabled, you can suppress it the same way:

```neon
parameters:
    ignoreErrors:
        -
            identifier: squidit.naming.interfaceBareName
            path: src/Legacy/*
```

Both stable and experimental identifiers support the same suppression syntax.

---

#### Experimental Support Utilities (Maintainers)

- `TypeCandidateResolver` resolves naming candidates from inferred PHPStan types.
- `VariableNameMatcher` validates variable naming against normalized base candidates.
- `DenyList` is an internal tuning object used by resolver/matcher flows to suppress specific class or candidate names when reducing false positives.
- This support layer is internal for experimental auto review rules and not part of the stable public API.

#### RuleTestCase Fixtures (Maintainers)

- For `PHPStan\Testing\RuleTestCase` fixtures in `tests/Unit/PHPStan/Rules/<Module>/Fixtures`, do not use autoloaded `SquidIT\Tests\...` namespaces.
- Use isolated fixture namespaces (for example `TypeSuffixMismatchFixtures\...`) so fixture symbols are only loaded through fixture analysis.
- Do not import dependency symbols from `tests/.../Runtime` into fixture files (including `@var` PHPDoc types); keep these dependencies in fixture-local namespaces.
- Avoid method-level `@param` PHPDoc on constructors with promoted properties in `RuleTestCase` fixtures; on Windows this can still trigger a post-coverage crash (`-1073741819`).
- Inline iterable `@var` narrowing fixtures in `RuleTestCase` can also be unstable on Windows coverage runs (`-1073741819`).
- If this pattern must be covered, skip only that specific test while `XDEBUG_MODE=coverage`.
- If a fixture file references other fixture classes/interfaces, add a rule-specific `.neon` file with `parameters.scanFiles` for those dependency fixture files.
- Register that `.neon` from the test class with `public static function getAdditionalConfigFiles(): array` to avoid `ReflectionProvider class not found` misconfiguration errors.
- Avoid manual `require_once` of fixture files in rule tests unless there is no alternative.
- Keep fixture-related `excludePaths` and `ignoreErrors` narrow and scoped to the exact fixture directory/identifier.
- This prevents coverage instability where `composer test:unit:coverage` can crash with Windows exit code `-1073741819` after report generation.
