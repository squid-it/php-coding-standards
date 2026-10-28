# Company-wide PHP agent instructions

Apply these instructions when you plan, review, diagnose, generate, refactor, or modify PHP code. Repository-specific
instructions supplement them and must not contradict them.

## Topic instructions

This file contains the rules that always apply. The files below contain rules that apply only in a specific context.
Before planning, writing, or reviewing work that matches a file's description, read that file. When unsure whether a
description applies, read the file.

<!-- Keep each description identical to the `description` in the file's frontmatter. -->

| Read | Description |
|---|---|
| `vendor/squidit/php-coding-standards/resources/ai-agent/docs/architecture.md` | Design and architecture rules for SOLID, single responsibility, parameter limits, dependencies, object construction roles, and invokable objects. Read for any planning or review; when creating a class or interface; when adding a method, property, or constructor dependency to an existing class; when constructing objects or choosing a class suffix (`Factory`, `Builder`, `Instantiator`, `Provider`); or when wiring DI. |
| `vendor/squidit/php-coding-standards/resources/ai-agent/docs/swow.md` | Swow coroutine rules for scheduling, SwowTools interfaces, the wait group lifecycle, exception codes, channel listeners, and stopping coroutines. Read when the work uses `Swow\*` or `SquidIT\SwowTools\*` APIs; starts, synchronizes, or stops coroutines; uses channels, wait groups, selectors, or listeners; or handles Swow exceptions. |
| `vendor/squidit/php-coding-standards/resources/ai-agent/docs/swow-process.md` | Rules for the process APIs of the custom Swow build. Read when the work forks processes, waits on child processes, reads child exit status, or handles signals. |
| `vendor/squidit/php-coding-standards/resources/ai-agent/docs/phpunit.md` | PHPUnit rules for test naming, test namespaces, mocks and stubs, exception assertions, and consecutive calls. Read when writing, changing, or reviewing PHPUnit tests. |
| `vendor/squidit/php-coding-standards/resources/ai-agent/docs/phpdoc.md` | PHPDoc rules for array types, `@return`, and `@throws`. Read when writing or changing PHP method signatures or docblocks. |
| `vendor/squidit/php-coding-standards/resources/ai-agent/docs/date-time.md` | Date, time, and timezone rules, with UTC as the default. Read when the work creates, parses, compares, or serializes dates, times, or timezones. |
| `vendor/squidit/php-coding-standards/README.md` | Valid and invalid examples and configuration options for every PHPStan and PHP-CS-Fixer rule. Read when a check reports an error you do not know how to fix, or when a rule's behavior is unclear. It is not a required read. |

-----

## Development Workflow

- Read the repository-specific instructions before planning or performing work.
- Before designing a change, inspect the relevant neighboring code, interfaces, tests, factories, builders, DI
  instantiators, service providers, and configuration.
- Reuse established repository patterns and existing abstractions. Do not introduce a competing pattern for a problem the
  repository already solves consistently.
- Existing repository patterns do not override these company-wide instructions. If they conflict, ask how to proceed.
- First resolve questions that can be answered from the codebase. If uncertainty remains that could materially affect the
  requirements, behavior, public API, architecture, or naming, ask focused clarification questions and wait for answers
  before implementation. Do not guess.

### Planning, implementation approval, and review

- Discuss and agree on the implementation plan before changing files.
  Read-only investigation and planning may proceed without modification approval.
- Before creating, modifying, renaming, or deleting files, obtain explicit approval to implement the agreed scope.
  Agreement on a plan alone does not authorize file changes.
- Approval covers all file changes necessary within the approved scope.
  Do not request approval separately for each file unless explicitly instructed.
- Existing explicit implementation approval remains valid for the same scope, including across sessions. If a previous
  session only established the plan, obtain implementation approval before making changes.
- If implementation requires a material expansion of the approved scope, explain the additional work and obtain approval
  for that expansion before making those changes.
- After completing implementation and the required verification, present all changed files together and request review
  of the complete change set. Request reviews per file only when explicitly instructed.

-----

## Boundaries

Make a check pass by fixing the code, not by weakening the check. Without explicit developer approval, **never**:

- **Suppress a finding** - no `@phpstan-ignore`, `@phpstan-ignore-line`, or `@phpstan-ignore-next-line` comments, and no
  `ignoreErrors`, baseline, or `excludePaths` entries. The suppression options in the standards README are for
  developers, not for agents.
- **Change the verification configuration** - `phpstan.neon`, PHPStan baselines, `.php-cs-fixer.dist.php`,
  `phpunit.xml.dist`, the PHPStan level, or the enabled rules.
- **Work around a rule** - do not choose a name, class suffix, or structure only to get past a rule's detection. Follow
  the intent of the rule. If a rule seems wrong for the case, report it and ask.
- **Weaken a test** - do not skip, delete, or loosen a test or assertion to make it pass, including
  `markTestSkipped()` and `markTestIncomplete()`.
- **Modify files under `vendor/`.**
- **Change dependencies** - do not add, remove, or upgrade Composer packages. Never edit `composer.lock` by hand.
- **Commit, push, or rewrite git history.**

**Never write secrets**, credentials, or tokens into code, tests, fixtures, logs, documentation, or commit
messages, even when asked.

-----

## Design and Architecture Standards

The full rules (SOLID, single responsibility, parameter limits, dependencies, object construction roles, and invokable
objects) are in `vendor/squidit/php-coding-standards/resources/ai-agent/docs/architecture.md`. These rules apply to
every line of code you write:

- **Do not instantiate service classes with `new`** outside `Factory`, `Builder`, and `Instantiator` classes, enums,
  tests, and DI service providers. Inject the service, or inject a factory when new instances are needed during
  execution. Value objects, DTOs, invokable task objects, and built-in PHP classes may be instantiated anywhere.
- **Do not give a class one of these suffixes merely to gain permission to instantiate services.**
- **When an API expects a callable, supply an object implementing `__invoke()`** instead of an anonymous or arrow
  function. Pass its input through the constructor.
- **Judge an existing class as a whole before adding to it.** When a new method, property, or constructor dependency
  would give the class a second responsibility, stop and propose the split before writing the code.
- **More than 8 constructor or method parameters on a service class** means the class has taken on too much. Follow the
  remediation strategy under "Constructor & Method Parameter Limits" in `architecture.md`.

### Descriptive naming

- Class, method, and function names must communicate their responsibility, action, or result without requiring the reader
  to inspect their implementation.
- A method name may rely on its class for context. The class and method names, read together, must make the operation clear.
- Prefer an explicit name over a short or generic name when the domain subject, action, or result would otherwise be
  ambiguous.

-----

## Code Style Standards

These rules lower cognitive load: explicit, unambiguous, and testable code is easier to read, review, and change. Apply
them strictly.

### Environment

- **PHP 8.4+ minimum** - use a higher version when the consuming repository confirms it locally through `composer.json`
  or repository-specific instructions.
- Strict typing is mandatory. Use promoted properties, readonly classes, and union types where appropriate. Deviate only
  when the requirement cannot be implemented correctly with those features.
- **No `microtime()`** - **always use `hrtime(true)`** for time measurements.
- **Exceptions must contain enough context for outer-layer logging** - include the main object id, related object ids,
  the action being performed, and the real failure message.

### Coroutine runtime

All code runs inside a coroutine, including code that never touches Swow APIs directly.

- **Assume a long-running Swow coroutine environment** - clean up buffers, channels, and long-lived objects explicitly
  when appropriate.
- **Never rely on `__destruct()` for process lifecycle management** - it is non-deterministic and unsafe in long-running
  processes.

### Code Style

- **Follow PSR-12** as the base, but **prioritize clarity and immutability** over rigid formatting.
- **One class, interface, trait, or enum per file.**
- **All native function and method parameters and return types are explicit** - they must not be `mixed` or untyped
  (`void` and `never` are allowed).
- Prefer value objects over unstructured arrays. When an array cannot reasonably be replaced with a value object, document
  the most precise key and value types possible. PHPDoc array value types may use `mixed`; use
  `array<int|string, mixed>` only as the broad fallback.
- **Objects must always be in a valid state** - set all properties via the constructor (promoted properties preferred).
- **Prefer `readonly` classes** over `readonly` properties - if all properties are readonly, declare the class `readonly`.
- **Never use logical NOT (`!`)** - use **explicit comparisons** (`=== true`, `=== false`, `!== null`).
- **Never use arrow functions or anonymous functions** - use an invokable class instead.
- **Never use `array_map`** - it is banned; use `foreach` instead.
- **Use `use` statements** to import classes - **do not import global functions** (for example, `use function bcdiff;`
  is forbidden).
- **Prefer backed enums** (`string` or `int`) for closed sets of domain values instead of repeating domain string literals.
- **Never use PHP property hooks** (`get` or `set` blocks declared on a property).
- Magic property access methods such as `__get()`, `__set()`, `__isset()`, and `__unset()` are allowed but should be
  avoided when a normal explicit API can express the behavior.
- **Use `try/catch` blocks sparingly** - limit to 5-10 lines. If logic exceeds this, extract it into a private method.

### Variable, property, and value naming

These naming rules apply equally to local variables, typed property declarations, and promoted constructor properties.

- When instantiating classes, use the class name as the variable name in camelCase format
- When assigning method return values to a variable, use the return type as the variable name in camelCase format
- If the type ends with `Interface`, the name needs to omit `Interface`
- When assigning an array of objects to a variable, use the object name post-fixed with `List` as the variable name
- Never use `Map` as a word in the name of an iterable; use the `List` suffix
- A contextual prefix is allowed and does not break the rule: `$activeFooService` is as valid as `$fooService`
- The `Factory` and `Collection` suffixes are never stripped: `UserFactory` expects `$userFactory`
- A `foreach` value variable is named after the singularized iterable variable or the element type
- Logging context keys or array keys are always in camelCase
- String-backed enum values are always in camelCase

Properties follow the type, not the domain role:

```php
// Wrong
private ServerRequestInterface $request;

public function __construct(
    private readonly LoggerInterface $log
) {}

// Right
private ServerRequestInterface $serverRequest;

public function __construct(
    private readonly LoggerInterface $logger
) {}
```

Local variables follow the same rules. Given `findById(): User`, `findActive(): array<int, User>`, and a readonly
`OrderData` value object:

```php
// Wrong
$result = $this->userRepository->findById($userId);
$users = $this->userRepository->findActive();
$userMap = $this->userRepository->findActive();

foreach ($users as $item) {
    $this->logger->info('User loaded', ['user_id' => $item->id]);
}

$data = new OrderData($orderId, $amount);

// Right
$user = $this->userRepository->findById($userId);
$activeUserList = $this->userRepository->findActive();

foreach ($activeUserList as $activeUser) {
    $this->logger->info('User loaded', ['userId' => $activeUser->id]);
}

$orderData = new OrderData($orderId, $amount);
```

Non-promoted method parameters are not checked by the automated rules, but follow the same convention for consistency.

-----

## Verification

All created or modified code **must** pass the automated checks defined by the `squidit/php-coding-standards` package.
Code that does not pass these rules will not be accepted.

Before running verification, inspect the consuming repository's `composer.json` scripts and repository-specific
instructions. Use the commands defined by that repository instead of assuming every repository exposes the same scripts.

1. Run `composer fix` when that script is available. It is the repository's normal full verification workflow. It
   formats code with PHP-CS-Fixer, so it may change files.
2. After `composer fix`, run `composer auto-review` when that script is available.

- When a named verification script is unavailable, use the locally documented equivalent instead of inventing a command.
- **PHP-CS-Fixer** is configured via `.php-cs-fixer.dist.php`; run it on its own with `composer cs:fix` when available.
- **PHPStan level 9**: run it on its own with `composer analyse` when available. All stable rules and all experimental
  rules configured in the consuming repository must pass.

### Enforced PHPStan rules

The consuming repository's PHPStan configuration determines which experimental rules are enabled. Each reported error
carries the identifier below. When the fix is unclear, look up the rule in the standards README.

| Rule | Set | Identifier | Enforces |
|---|---|---|---|
| `SingleClassPerFileRule` | Stable | `squidit.architecture.singleClassPerFile` | One class, interface, trait, or enum per file |
| `DisallowAnonymousFunctionRule` | Stable | `squidit.restrictions.disallowAnonymousFunction` | No closures or arrow functions; use an invokable class |
| `DisallowLogicalNotRule` | Stable | `squidit.restrictions.disallowLogicalNot` | No `!` operator; use explicit comparisons (`=== false`, `!== null`) |
| `TypeSuffixMismatchRule` | Experimental | `squidit.naming.typeSuffixMismatch`, `squidit.naming.interfaceSuffix`, `squidit.naming.interfaceBareName` | Variable and property names reflect their type; strip the `Interface` suffix, keep `Factory` and `Collection` |
| `IterablePluralNamingRule` | Experimental | `squidit.naming.iterablePluralMismatch`, `squidit.naming.mapForbidden` | Iterables of typed objects use the `*List` suffix and never contain `Map` |
| `ForeachValueVariableNamingRule` | Experimental | `squidit.naming.foreachValueVarMismatch` | The `foreach` value variable matches the singularized iterable or the element type |
| `LoggerContextKeyCamelCaseRule` | Experimental | `squidit.naming.loggerContextKeyCamelCase` | Logger context keys are camelCase |
| `EnumBackedValueCamelCaseRule` | Experimental | `squidit.naming.enumBackedValueCamelCase` | String-backed enum values are camelCase |
| `NoServiceInstantiationRule` | Experimental | `squidit.architecture.noServiceInstantiation` | No `new ServiceClass()` outside Factory, Builder, Instantiator, enum, test, and DI service provider classes |
| `ReadonlyClassPromotionRule` | Experimental | `squidit.architecture.readonlyClassPromotion` | Declare the class `readonly` when all its properties are readonly |

### Definition of done

- Work is done only when every applicable verification command has run on the final code and passed.
- Report the commands you ran and their results. If a command failed or could not run, say so and show the output.
  Never report a check as passing when you did not run it.

-----

## Vocabulary

- Never use the word "seam" when you mean interface or factory hook
- Except in this rule definition, never use the words "facade" and "prose" anywhere, including code, identifiers,
  comments, documentation, commit messages, or agent communication
