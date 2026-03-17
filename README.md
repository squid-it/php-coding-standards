# PHP Coding Standards - PHP-CS-Fixer Rules

Default coding standard rules for PHP-CS-Fixer.

## Usage

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

## Manual Triggering

Run following command in your project directory, that will run fixer for every `.php` file.

```bash
vendor/bin/php-cs-fixer fix
```

---

# PHPStan Rules

This library provides two sets of custom PHPStan rules:

* **Stable rules** — enforced conventions with a low false-positive rate, ready for CI.
* **Experimental auto review rules** — naming and architecture checks that support automated code review. Opt-in only.

---

### Stable Rules

Add the following to your project's `phpstan.neon`:

```neon
rules:
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Architecture\SingleClassPerFileRule
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Restrictions\DisallowAnonymousFunctionRule
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Restrictions\DisallowLogicalNotRule
```

### Stable Rules Reference

| Rule | Identifier | Description |
| --- | --- | --- |
| `SingleClassPerFileRule` | `squidit.architecture.singleClassPerFile` | Enforces that each PHP file contains only one class-like declaration (class, interface, trait, or enum). Anonymous classes are allowed. |
| `DisallowAnonymousFunctionRule` | `squidit.restrictions.disallowAnonymousFunction` | Disallows anonymous functions (closures) and arrow functions. Use an invokable class with an `__invoke()` method instead. |
| `DisallowLogicalNotRule` | `squidit.restrictions.disallowLogicalNot` | Disallows the logical NOT operator (`!`). Use explicit comparisons instead (`=== true`, `=== false`, `!== null`). |


You are completely right, the Stable Rules section is looking a bit bare without any practical examples to anchor the descriptions.

Let's fix that by adding clear **Valid** and **Invalid** code snippets for each of the three stable rules based on your codebase.

Here are the proposed examples we can append to the **Stable Rules Reference** section:

#### `SingleClassPerFileRule`

This rule ensures files are focused and predictable by limiting them to a single named declaration. Anonymous classes are inherently exempt as they don't declare a new named symbol in the namespace.

**Valid:**

```php
// File: src/UserService.php
class UserService 
{
    public function createHandler(): object
    {
        return new class() {}; // Anonymous classes are allowed
    }
}

```

**Invalid:**

```php
// File: src/UserService.php
class UserService {}

class UserDto {} // squidit.architecture.singleClassPerFile

```

#### `DisallowAnonymousFunctionRule`

This rule prevents closures and arrow functions, encouraging isolated, testable invokable objects instead. This is especially useful for cleanly passing state into coroutines without relying on the `use` statement.

**Valid:**

```php
final readonly class ProcessTask
{
    public function __construct(
        private int $taskId,
    ) {}

    public function __invoke(): void
    {
        // Process the task using $this->taskId
    }
}

Swow\Coroutine::run(new ProcessTask(123));

```

**Invalid:**

```php
$taskId = 123;

Swow\Coroutine::run(function () use ($taskId) {
    // squidit.restrictions.disallowAnonymousFunction
});

$mapper = fn($item) => $item->id; // squidit.restrictions.disallowAnonymousFunction

```

#### `DisallowLogicalNotRule`

This rule forces explicit comparisons to make conditional logic completely unambiguous.

**Valid:**

```php
if ($isReady === false) {
    // ...
}

if ($user !== null) {
    // ...
}

```

**Invalid:**

```php
if (!$isReady) {
    // squidit.restrictions.disallowLogicalNot
}

if (!$user) {
    // squidit.restrictions.disallowLogicalNot
}

```

---

### Experimental Auto review Rules

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
    - SquidIT\PhpCodingStandards\PHPStan\Rules\Architecture\ReadonlyClassPromotionRule
```

### Experimental Rules Reference

| Rule | Identifier(s) | Description |
| --- | --- | --- |
| `TypeSuffixMismatchRule` | `squidit.naming.typeSuffixMismatch` | Enforces that typed properties, promoted parameters, and local variable assignments are named consistently with their inferred type. |
| `TypeSuffixMismatchRule` | `squidit.naming.interfaceSuffix` | Always-on check that reports interface-typed names ending with `Interface` and suggests dropping the suffix. |
| `TypeSuffixMismatchRule` | `squidit.naming.interfaceBareName` | Optional check (disabled by default) that reports when a variable or property name uses an interface-derived base name without a contextual prefix. |
| `IterablePluralNamingRule` | `squidit.naming.iterablePluralMismatch` | Enforces `*List` naming when an assignment holds an iterable of typed objects. |
| `IterablePluralNamingRule` | `squidit.naming.mapForbidden` | Reports when an iterable assignment target contains the word segment `Map`. |
| `ForeachValueVariableNamingRule` | `squidit.naming.foreachValueVarMismatch` | Enforces that the foreach value variable is named after the singularized iterable variable or the inferred element type. |
| `LoggerContextKeyCamelCaseRule` | `squidit.naming.loggerContextKeyCamelCase` | Enforces camelCase for string-literal keys in the context array argument of `Psr\Log\LoggerInterface` method calls. |
| `EnumBackedValueCamelCaseRule` | `squidit.naming.enumBackedValueCamelCase` | Enforces camelCase backed string values on string-backed enums, with an exception when the same literal is returned by a `to*()` method. |
| `NoServiceInstantiationRule` | `squidit.architecture.noServiceInstantiation` | Disallows `new` expressions for service classes outside of creator classes (`*Factory`, `*Builder`, `*Provider`), with configurable enum/test/fixture skips. |
| `ReadonlyClassPromotionRule` | `squidit.architecture.readonlyClassPromotion` | Suggests promoting a class to `readonly` when all declared properties are individually readonly, with safety guards for inheritance-sensitive cases. |

---

#### `TypeSuffixMismatchRule`

Checks typed properties, promoted constructor parameters, and local variable assignments. The variable or property name must reflect the inferred type — either as an exact base name match or with a contextual prefix.

By default, this rule enforces `squidit.naming.typeSuffixMismatch` and `squidit.naming.interfaceSuffix`. The optional interface bare-name check (`squidit.naming.interfaceBareName`) is disabled by default.

**Name Normalization Rules:**
When resolving type candidate names, the rule applies the following normalizations to the short class name:
- **Mandatory Prefix Stripping:** The prefix `Abstract` is always removed (e.g., `AbstractService` expects `$service`).
- **Mandatory Suffix Stripping:** The suffixes `Interface`, `Abstract`, and `Trait` are always removed (e.g., `LoggerInterface` expects `$logger`).
- **Optional Suffix Stripping:** For the suffixes `Dto`, `Vo`, and `Entity`, both the stripped and unstripped forms are allowed (e.g., `UserDto` allows both `$user` and `$userDto`).
- **Never Stripped:** The suffixes `Factory` and `Collection` are strictly preserved (e.g., `UserFactory` expects `$userFactory`).

Docblock narrowing support:

* Assignment statements: inline `@var` on the assignment statement is used.
* Typed properties: property-level `@var` is used.
* Promoted properties: parameter-level `@var` is used.
* Promoted properties: constructor docblock `@param` is used.

Template-aware narrowing behavior:

* Template references in `@var` / `@param` (for example `TConnection`) are resolved against the active PHPStan template list from class and function scope.
* Supported template declarations include `@template T`, `@template T of FooData`, and template bounds propagated through generic `@extends` / `@implements` when PHPStan exposes them in scope.
* Unbounded templates (`@template T`) and broad bounds such as `@template T of object` do not produce a concrete class-name candidate on their own, so the rule allows contextual naming without forcing `tConnection`.
* Concrete bounds (for example `@template T of FooData` or `@template T of FooData|BarData`) are enforced as normal type candidates.
* For assignment `@var`, when a template reference cannot be narrowed to concrete object class names, the rule falls back to the inferred assignment expression type.

**Aggregate List Aliasing:**
The rule contains specific logic to validate aggregate list aliases. A variable ending in `List` is considered valid if the inferred type candidate's base name ends in `Aggregate`, provided the preceding stems of both names match (for example, `$definitionList` is valid for `DefinitionAggregate`).

**Valid:**

```php
private FooService $fooService;
private FooService $activeFooService;
$definitionList = $this->definitionAggregate; // inferred type: DefinitionAggregate
```

**Invalid:**

```php
private FooService $item;          // squidit.naming.typeSuffixMismatch
private FooServiceInterface $fooServiceInterface;    // squidit.naming.interfaceSuffix
```

The `interfaceSuffix` identifier fires when an interface-typed name ends with `Interface`. For example, use `$readChannel` instead of `$readChannelInterface`.

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
            enableInterfaceBareNameCheck: true
```

Do not also list the rule under `rules:` when using `services:` wiring - PHPStan would register it twice.

---

#### `IterablePluralNamingRule`

Checks assignment targets where the inferred type is an iterable of typed objects. The variable or property name must end with `List` and still match the inferred element type.

Docblock narrowing support:

* Assignment statements: inline `@var` on the assignment statement is used to resolve iterable value type candidates (named and unnamed `@var`).

**Required collection suffix:** `List`

**Valid:**

```php
$nodeList = [$node];
$activeNodeList = [$node];
$primaryNodeList = ['id' => $node]; // associative keys are allowed
$this->nodeList = [$node]; // property assignments are also checked
```

**Invalid:**

```php
$nodes = [$node];        // squidit.naming.iterablePluralMismatch
$nodeById = [$node];     // squidit.naming.iterablePluralMismatch
$nodeMap = [$node];      // squidit.naming.mapForbidden
```

The `mapForbidden` identifier fires whenever the name contains `Map` as a camelCase word segment (for example `nodeMap`, `nodeMapById`). Use `*List` naming instead.

---

#### `ForeachValueVariableNamingRule`

Checks the value variable in a `foreach` statement. Naming candidates are resolved from two sources: the singularized iterable variable name and the inferred iterable element type. When singularizing the iterable variable name, the rule first strips one collection suffix if present (`List`, `Collection`, `Lookup`, `ById`, or `ByKey`) before depluralizing the remaining word. The value variable must match one of the resulting candidates directly, or use it as a suffix with a contextual prefix.

Additional allowed patterns:

* `*Key => *Value` and `*Index => *Value` pairs are accepted when key/value share the same stem (for example `$settingKey => $settingValue`).
* Value names ending with `Value` are accepted when derived from the iterable variable stem (for example `$settings` -> `$settingValue`).
* Iterables typed as `RecursiveIterator` / `RecursiveIteratorIterator` are skipped by this rule.

**Valid** (given `$children` typed as `array<int, ChildNode>`):

```php
foreach ($children as $child) {}
foreach ($children as $childNode) {}
foreach ($children as $firstChildNode) {}
foreach ($settings as $settingValue) {}
foreach ($settings as $settingKey => $settingValue) {}
foreach ($settings as $settingIndex => $settingValue) {}
```

**Invalid:**

```php
foreach ($children as $item) {}    // squidit.naming.foreachValueVarMismatch
```

---

#### `LoggerContextKeyCamelCaseRule`

Checks string-literal keys in the context array argument of PSR logger calls. Applies only when the receiver type is compatible with `Psr\Log\LoggerInterface`. Dynamic keys and non-logger receivers are ignored.

Context argument positions:

* `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug` — second argument (index `1`)
* `log` — third argument (index `2`)

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

#### `EnumBackedValueCamelCaseRule`

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

#### `NoServiceInstantiationRule`

Disallows `new` expressions for service classes in non-creator classes. A class is exempt when:

* The file path is inside a directory declared in the nearest `composer.json` `autoload-dev.psr-4` section (only when `excludeComposerDevDirs: true`), or
* The containing class extends `PHPUnit\Framework\TestCase` (when `skipPhpUnitTestCaseClasses: true`, default), or
* The containing scope is an enum (when `allowInstantiationInEnums: true`, default), or
* The containing class name ends with a creator suffix (`Factory`, `Builder`, or `Provider` by default), or
* The instantiated class is an internal/builtin PHP class (for example `DateTimeImmutable`), or
* The instantiated class passes the VO/DTO classifier gates, or
* The instantiation occurs outside a method scope (e.g., in default property values or promoted constructor properties), or


* The instantiation is an anonymous class.



**Valid:**

```php
// Inside a *Factory, *Builder, or *Provider class
final readonly class HttpClientFactory
{
    public function create(): HttpClient
    {
        return new HttpClient();    // allowed
    }
}

// VO/DTO and internal class instantiation anywhere
$orderDto = new OrderDto($id, $amount);
$dateTime = new DateTimeImmutable();

enum BuiltInServiceType
{
    case Signals;
    case ControlApi;

    public function createBuiltInService(): object
    {
        return match ($this) {
            self::Signals => new SwowSignalHandler(),
            self::ControlApi => new BuiltInControlApiSystemService(),
        };
    }
}

// Instantiation outside method scope (promoted properties) or anonymous classes
final readonly class ApiService
{
    public function __construct(
        private HttpClient $httpClient = new HttpClient(), // allowed outside method scope
    ) {}

    public function createHandler(): object
    {
        return new class() {}; // allowed anonymous class
    }
}
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
* The class is declared `readonly`, or
* All declared and inherited non-static instance properties are `readonly`.


2. **Public API gate** (all must be true):
* Public methods are limited to: `__construct`, exact names (`toArray`, `jsonSerialize`, `__toString`, `equals`, `equalsTo`, `with`), or methods starting with allowed prefixes (`get`, `is`, `has`, `from`, `to`).


* Any other declared public method disqualifies the class.
* Prefixes `get`, `is`, `has`, `from`, `to` and `with` require a camelCase word boundary — `getOrder()` qualifies, `getter()` does not.





**Configuring creator suffixes and enum/test/fixture skips:**

By default, the rule allows instantiation inside enums and any class ending with `Factory`, `Builder`, or `Provider`, and it skips classes extending `PHPUnit\Framework\TestCase`.

If you want custom suffixes or skip behavior, remove `NoServiceInstantiationRule` from `rules:` and register it via `services`:

```neon
services:
    -
        class: SquidIT\PhpCodingStandards\PHPStan\Rules\Architecture\NoServiceInstantiationRule
        tags:
            - phpstan.rules.rule
        arguments:
            allowedCreatorClassSuffixList:
                - Factory
                - Builder
                - Provider
                - Assembler
            allowInstantiationInEnums: true
            skipPhpUnitTestCaseClasses: true
            excludeComposerDevDirs: false
```

Do not also list the rule under `rules:` when using `services:` wiring - PHPStan would register it twice. An empty list falls back to the defaults (`Factory`, `Builder`, `Provider`).
`allowInstantiationInEnums`:

* Default: `true`
* Set to `false` to enforce this rule inside enum methods too (opt out from enum instantiation allowance).

`skipPhpUnitTestCaseClasses`:

* Default: `true`
* Set to `false` to enforce this rule inside PHPUnit test classes as well.

`excludeComposerDevDirs`:

* Default: `false`
* Set to `true` to skip this rule for files under directories declared in `autoload-dev.psr-4` of the nearest `composer.json`.
* Example: `"SquidIT\\Tests\\PhpCodingStandards\\": "tests"` excludes `tests/*`.

**Opt out example (disallow enum instantiation):**

```neon
services:
    -
        class: SquidIT\PhpCodingStandards\PHPStan\Rules\Architecture\NoServiceInstantiationRule
        tags:
            - phpstan.rules.rule
        arguments:
            allowInstantiationInEnums: false
```

---

#### `ReadonlyClassPromotionRule`

Suggests class-level readonly promotion when all declared properties are already individually `readonly`.

This rule reports only when all the following conditions are true:

* The class is `final`.
* The class does not extend another class.
* The class is not already declared `readonly`.
* The class declares at least one property (explicit or promoted).
* Every declared property is individually marked `readonly`.

This keeps the rule conservative and avoids suggesting contract changes that can break inheritance.

**Valid:**

```php
class NonFinalConfig
{
    public readonly string $dsn;
}

final class FinalConfig
{
    public readonly string $dsn;
    private string $env; // mutable property, no suggestion
}
```

**Invalid:**

```php
final class FinalConfig
{
    public readonly string $dsn;
    private readonly string $env;
    // squidit.architecture.readonlyClassPromotion
}
```

When this identifier is reported, you can promote the class:

* Change `final class FinalConfig` to `final readonly class FinalConfig`.
* Remove property-level `readonly` modifiers from that class.

---

### Suppressing Rules

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

You can suppress `squidit.naming.interfaceSuffix` the same way:

```neon
parameters:
    ignoreErrors:
        -
            identifier: squidit.naming.interfaceSuffix
            path: src/Legacy/*
```

When the optional interface bare-name check is enabled, you can suppress it as well:

```neon
parameters:
    ignoreErrors:
        -
            identifier: squidit.naming.interfaceBareName
            path: src/Legacy/*
```

Both stable and experimental identifiers support the same suppression syntax.

#### Baseline and Ignore Lists (Migration to `*List`)

If you are enabling strict iterable `*List` naming on an existing codebase, you can keep CI green while gradually migrating legacy names.

Generate a baseline from your auto review config:

```bash
vendor/bin/phpstan analyse -c phpstan-autoreview.neon --generate-baseline=phpstan-baseline.neon
```

Include that baseline in your project config:

```neon
includes:
    - phpstan-baseline.neon
```

If you prefer an explicit ignore list instead of a baseline, use targeted ignores:

```neon
parameters:
    ignoreErrors:
        -
            identifier: squidit.naming.iterablePluralMismatch
            path: src/Legacy/*
        -
            identifier: squidit.naming.mapForbidden
            path: src/Legacy/*
```

Narrow these ignores to the smallest possible paths and remove them as files are renamed to `*List`.

---

#### Experimental Support Utilities (Maintainers)

* `TypeCandidateResolver` resolves naming candidates from inferred PHPStan types.
* `VariableNameMatcher` validates variable naming against normalized base candidates.
* `DenyList` is an internal tuning object used by resolver/matcher flows to suppress specific class or candidate names when reducing false positives.
* This support layer is internal for experimental auto review rules and not part of the stable public API.

#### RuleTestCase Fixtures (Maintainers)

* For `PHPStan\Testing\RuleTestCase` fixtures in `tests/Unit/PHPStan/Rules/<Module>/Fixtures`, do not use autoloaded `SquidIT\Tests\...` namespaces.
* Use isolated fixture namespaces (for example `TypeSuffixMismatchFixtures\...`) so fixture symbols are only loaded through fixture analysis.
* Do not import dependency symbols from `tests/.../Runtime` into fixture files (including `@var` PHPDoc types); keep these dependencies in fixture-local namespaces.
* Avoid method-level `@param` PHPDoc on constructors with promoted properties in `RuleTestCase` fixtures; on Windows this can still trigger a post-coverage crash (`-1073741819`).
* Inline iterable `@var` narrowing fixtures in `RuleTestCase` can also be unstable on Windows coverage runs (`-1073741819`).
* Template-bound fixture assertions in `TypeSuffixMismatchRuleTest` are also skipped in coverage mode on Windows for the same post-coverage crash pattern (`-1073741819`).
* If this pattern must be covered, skip only that specific test while `XDEBUG_MODE=coverage`.
* If a fixture file references other fixture classes/interfaces, add a rule-specific `.neon` file with `parameters.scanFiles` for those dependency fixture files.
* Register that `.neon` from the test class with `public static function getAdditionalConfigFiles(): array` to avoid `ReflectionProvider class not found` misconfiguration errors.
* Avoid manual `require_once` of fixture files in rule tests unless there is no alternative.
* Keep fixture-related `excludePaths` and `ignoreErrors` narrow and scoped to the exact fixture directory/identifier.
* Coverage mitigation playbook:
* Prefer plain `PHPUnit\Framework\TestCase` tests for rule-internal branch coverage when fixture analysis is not required.
* Use `#[RunInSeparateProcess]` for tests that must exercise fallback paths tied to missing PHPStan static reflection state.
* Keep `RuleTestCase` fixture coverage minimal on Windows in `XDEBUG_MODE=coverage`; skip only the exact unstable cases.
* Keep fixture dependencies in isolated fixture namespaces and always register cross-file dependencies in `.neon` `scanFiles`.
* This prevents coverage instability where `composer test:unit:coverage` can crash with Windows exit code `-1073741819` after report generation.

---
