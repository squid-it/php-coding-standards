Below is a **detailed markdown development plan**, broken into **small “one session” segments**. It’s designed to stay **memory-light** by keeping every rule **stateless and per-node** (no global collectors, no whole-project scans), with only small string-based caches for derived names / VO classification.

---

# SquidIT PHPStan Naming Rules – Development Plan

## Goals

* Enforce naming conventions to reduce cognitive load and review churn.
* Keep rules **experimental/optional** via a dedicated PHPStan config + composer script.
* Keep runtime/memory overhead minimal: **local checks** only, plus small caches.

## Non-goals (v1)

* Destructuring assignments.
* Catch variables.
* Enforcing “descriptive names” beyond suffix/prefix/type heuristics.
* Enforcing array-key camelCase *everywhere* (scoped to LoggerInterface context only for v1).

---

## Package and namespaces

* Namespace root: `SquidIT\PhpCodingStandards\PHPStan\Rules`
* Error identifiers: **must start with** `squidit.naming*`

Suggested structure:

```
src/PHPStan/Rules/
src/PHPStan/Support/   (helpers)
tests/PHPStan/Rules/
phpstan-naming.neon
```

---

## Shared helpers (build once, reuse everywhere)

These are critical for correctness and low false positives.

### 1) `NameNormalizer`

* Convert short class name to preferred camelCase (handle initialisms nicely: `HTTPClient` → `httpClient`)
* Strip suffixes (short-name only):

    * Always strip: `Interface`, `Abstract`, `Trait`
    * Optionally allow stripped variants for: `Dto`, `Vo`, `Entity` (case-insensitive)
    * **Never strip**: `Collection`, `Factory`
* Build “allowed base names” set for a class name:

    * includes stripped and unstripped forms where applicable

### 2) `TypeCandidateResolver`

Given a PHPStan `Type`, produce allowed “base names”:

* Strip `null` and `false` from unions
* Consider only **named object types** (no scalars)
* Expand to hierarchy candidates (self + parents + interfaces)

    * Exclude internal/builtin candidates (per your requirement)
    * (Optional) also apply deny-list: `Traversable`, `IteratorAggregate`, etc. (we’ll still keep it as a safety net)
* Return a de-duplicated list of allowed base names

### 3) `VariableNameMatcher`

Given `$varName` and a list of base names:

* Valid if:

    * `$varName === $baseName` OR
    * `str_ends_with($varName, ucfirst($baseName))`
* Special “interface bare-name notice”:

    * If inferred *primary* type is an interface and `$varName === $baseName`, emit a **secondary notice-level diagnostic** (see rule identifiers below)

### 4) `Pluralizer / Singularizer`

For arrays/iterables-of-objects:

* pluralize base (`company` → `companies`, `class` → `classes`, default `+s`)
* singularize variable names for foreach:

    * remove suffixes: `List`, `Collection`, `Lookup`, `ById`, `ByKey`
    * reverse plural rules: `ies → y`, `es →`, `s →`

### 5) `VoDtoClassifier` (for the “no new services” rule)

Classifies *instantiated class* as VO/DTO if:

* Instantiated class is **internal** → allowed automatically (not “VO”, but “allowed”)
* Otherwise VO/DTO if:

    * class is `readonly` (or all properties readonly), AND
    * all **public methods** are in allowlist patterns:

        * `__construct`
        * getters: `get*`, `is*`, `has*`
        * pure-ish: `toArray`, `jsonSerialize`, `__toString`, `equals`, `equalsTo`
    * private/protected methods are allowed (any)
* Cached by FQCN to avoid repeating reflection

### 6) `ContainingClassResolver`

For a node scope, determine the containing class name and whether it’s a “Factory class”:

* Factory exception: containing class short name ends with `Factory` (v1)

---

## Rules list (v1)

### Rule 1: Variable/property name must match inferred object type suffix

* Applies to:

    * assignments (`$x = <expr>`)
    * typed properties (`private Foo $bar`)
    * promoted properties (`__construct(private Foo $bar)`)
* Expressions covered:

    * `new`
    * `clone`
    * any call where PHPStan infers a named object return type:

        * `ClassName::create/build/from*`
        * normal method calls
* Skips scalars entirely

Identifiers:

* `squidit.namingTypeSuffixMismatch` (hard error)
* `squidit.namingInterfaceBareNameNotice` (“notice” message; still reported, but you can choose to ignore or baseline separately)

### Rule 2: Arrays/iterables of objects must be plural (and “Map” is forbidden)

* Applies to same places as Rule 1 (assignment/property/promoted property)
* Only triggers if inferred type is iterable of named objects (value type is object)
* Valid naming forms (type base = `node` → plural = `nodes`):

    * `nodes`, `nodeList`, `nodeCollection`
    * `nodeById`, `nodeByKey`, `nodeLookup`
    * prefixes allowed: `activeNodeList`, `cachedNodes`, etc.
* Hard error if name contains `map`/`Map` anywhere:

    * `squidit.namingMapForbidden` (hard error)

Identifiers:

* `squidit.namingIterablePluralMismatch` (hard error)
* `squidit.namingMapForbidden` (hard error)

### Rule 3: Foreach element variable naming

* Applies to `foreach ($iterable as $valueVar)` (ignore key var for v1)
* Allowed names include:

    * singularized form of iterable variable name (with prefixing allowed by suffix rule)
    * element type suffix (with prefixing allowed)
    * combined: singularized + elementType (e.g. `childNode`), and prefixed variants (`firstChildNode`)
* If iterable variable name can’t be singularized, fall back to element type suffix validation.

Identifier:

* `squidit.namingForeachValueVarMismatch` (hard error)

### Rule 4: Logger context keys must be camelCase (string-literal keys only)

* Applies only to calls on objects that PHPStan can prove are `Psr\Log\LoggerInterface`
* Only inspects the **context argument** (2nd arg)
* Only enforces for **string-literal keys**
* v1 camelCase rule: reject snake_case (keys containing `_`)

Identifier:

* `squidit.namingLoggerContextKeyCamelCase` (hard error)

### Rule 5: Enum backed values camelCase, with conditional exception

* Applies to `enum` nodes (backed enums only)
* If backed value is a string literal:

    * must be camelCase **unless**
    * enum contains a `to*()` method that **references** that exact string literal anywhere in its body

        * we don’t care what it converts to — presence of conversion referencing that value is enough
* (We’ll keep matching simple and fast: scan method body for the literal string)

Identifier:

* `squidit.namingEnumBackedValueCamelCase` (hard error)

### Rule 6: No instantiation of non-VO/DTO inside non-VO/DTO classes (Factory exception)

* Applies to `new ClassName(...)` inside class methods (including `__construct`)
* Allowed if:

    * containing class is a `*Factory` (short name ends with `Factory`)
    * OR instantiated class is internal/builtin
    * OR instantiated class is classified as VO/DTO by `VoDtoClassifier`
* Otherwise error with message: “Inject this dependency or use a factory.”

Identifier:

* `squidit.namingNoServiceInstantiation` (hard error)

---

## Memory/performance guardrails

* No project-wide registries, no collectors, no preprocessing passes.
* Rule instances keep only small caches:

    * `fqcn → baseNameList`
    * `fqcn → isVoDto`
* Never store AST nodes in caches (only strings/booleans).
* Use early exits aggressively (skip scalars, skip unknown/dynamic class names, skip missing types).

---

# Development sessions

## Session 1 — Scaffold + test harness

**Deliverables**

* Create package skeleton + autoloading
* Add `phpstan-naming.neon` registering rules
* Add PHPStan RuleTestCase setup
* Add `README.md` describing “experimental ruleset” usage

**Acceptance**

* `vendor/bin/phpunit` runs rule tests (even if empty)
* `vendor/bin/phpstan analyse -c phpstan-naming.neon` loads extension without errors

---

## Session 2 — Core support utilities

**Deliverables**

* Implement `NameNormalizer`
* Implement `Pluralizer/Singularizer`
* Unit tests for:

    * suffix stripping rules (`Interface/Abstract/Trait`, allow Dto/Vo/Entity variants)
    * “don’t strip Collection/Factory”
    * initialism camelCase normalization
    * pluralization and singularization examples

**Acceptance**

* Helpers tested independently (fast feedback)

---

## Session 3 — Type resolution + matching engine

**Deliverables**

* Implement `TypeCandidateResolver`:

    * union filtering (ignore null/false)
    * hierarchy expansion (excluding internal/builtin)
* Implement `VariableNameMatcher` (exact or endsWith ucfirst)
* Add deny-list plumbing (even if initially empty) so we can tune later without refactors

**Acceptance**

* Tests for:

    * union handling (`Foo|null|false`)
    * interface hierarchy allowing base interface names
    * internal/builtin exclusion

---

## Session 4 — Rule 1: Type suffix mismatch (+ interface bare-name notice)

**Deliverables**

* Implement rule for:

    * `Assign`
    * `Property`
    * promoted `Param` (visibility flags)
* Emit:

    * hard error when mismatch
    * “notice” diagnostic (separate identifier) when variable equals bare interface base name

**Acceptance**

* Fixtures for:

    * `private Foo $bar` fails, `$barFoo` passes, `$foo` passes
    * interface bare name triggers notice (`ChannelInterface $channel`)
    * union types allow either suffix
    * `clone` assignment passes with suffix rule

---

## Session 5 — Rule 2: Iterable plural naming + Map forbidden

**Deliverables**

* Detect “iterable of objects” via PHPStan iterable value type + PHPDoc inferred generics
* Enforce allowed plural forms based on element type base name(s)
* Enforce hard error for `*Map*` substring anywhere in variable/property names

**Acceptance**

* Fixtures:

    * `/** @var array<int, Node> */ $nodes` valid as `$nodes`, `$nodeList`, `$activeNodes`, `$nodeById`
    * `$nodeMap` hard error
    * associative arrays allowed under the same plural conventions (v1)

---

## Session 6 — Rule 3: Foreach element variable naming

**Deliverables**

* Implement foreach rule:

    * singularize iterable variable name when available
    * get iterable value type base names
    * allow:

        * singular
        * type suffix
        * singular+type
        * all with prefixing via endsWith logic
* Skip destructuring patterns; ignore key-var naming

**Acceptance**

* Fixtures:

    * `$children as $child` allowed
    * `$children as $childNode` allowed if element type Node
    * `$children as $firstChildNode` allowed (suffix match)
    * `$children as $item` rejected (unless type supports `item` via type base, which it won’t)

---

## Session 7 — Rule 4: Logger context camelCase keys (scoped)

**Deliverables**

* Implement rule on `MethodCall`:

    * receiver type implements `Psr\Log\LoggerInterface`
    * context arg is array literal
    * keys are string literals
    * reject snake_case keys (contains `_`)

**Acceptance**

* Fixtures:

    * `$logger->info('x', ['fooBar' => 1])` ok
    * `$logger->info('x', ['foo_bar' => 1])` error
    * `$logger->info('x', [$key => 1])` skipped
    * non-logger arrays are not checked

---

## Session 8 — Rule 5: Enum backed value camelCase + `to*()` exception

**Deliverables**

* Implement `Enum_` rule:

    * for each case with string backed value:

        * if camelCase → ok
        * if snake_case:

            * scan enum methods named `to[A-Z].*`
            * accept if any method body contains that exact string literal
* Keep scanning local to enum node (no global state)

**Acceptance**

* Fixtures:

    * `case Foo = 'fooBar'` ok
    * `case Foo = 'foo_bar'` error if no conversion
    * `case Foo = 'foo_bar'` ok if there’s `public function toDb(): string { return 'foo_bar'; }`

---

## Session 9 — Rule 6: No service instantiation rule (VO/DTO classifier + Factory exception)

**Deliverables**

* Implement `new` rule:

    * find containing class
    * allow if containing class endsWith `Factory`
    * allow if instantiated class is internal/builtin
    * allow if instantiated class is VO/DTO by `VoDtoClassifier`
    * else error with “Inject or use factory” guidance
* Implement `VoDtoClassifier` allowlist of public methods + readonly requirement
* Cache classification by FQCN

**Acceptance**

* Fixtures:

    * In non-factory class: `new HttpClient()` → error
    * In `FooFactory`: `new HttpClient()` → ok
    * `new DateTimeImmutable()` → ok
    * `new SomeDto()` with readonly + only getters → ok
    * readonly service with `handle()` should **not** classify as DTO (method allowlist rejects it)

---

## Session 10 — Tuning + documentation + rollout strategy

**Deliverables**

* Document each identifier and examples
* Provide “recommended ignore patterns” for notice-level findings
* Provide a baseline workflow for existing code
* Add a “tuning checklist” (deny-list updates, method allowlist for VO classifier, pluralization edge cases)

**Acceptance**

* A new developer can:

    * install
    * run `phpstan -c phpstan-naming.neon`
    * understand each error and how to suppress it appropriately

---

## Proposed identifier catalog (v1)

* `squidit.namingTypeSuffixMismatch`
* `squidit.namingInterfaceBareNameNotice`
* `squidit.namingIterablePluralMismatch`
* `squidit.namingMapForbidden`
* `squidit.namingForeachValueVarMismatch`
* `squidit.namingLoggerContextKeyCamelCase`
* `squidit.namingEnumBackedValueCamelCase`
* `squidit.namingNoServiceInstantiation`

---





