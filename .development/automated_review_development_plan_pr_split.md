
# SquidIT PHPStan Naming Ruleset (Experimental) — Repo-Ready Checklist

This is a concrete, PR-by-PR build checklist (no PHP code yet), with file lists and a test fixture matrix.

---

## Repository layout (target)

```.
├─ src/
│  └─ PHPStan/
│     ├─ Rules/
│     │  ├─ Naming/
│     │  └─ Architecture/
│     └─ Support/
├─ tests/
│  ├─ Unit/
│  │  └─ PHPStan/
│  │     ├─ Rules/
│  │     │  ├─ Naming/
│  │     │  └─ Architecture/
│  │     └─ Support/
│  └─ Fixtures/
│     ├─ Naming/
│     ├─ Logger/
│     ├─ Enum/
│     └─ Architecture/
├─ phpstan-naming.neon
├─ phpunit.xml
├─ composer.json
├─ README.md
└─ DEVELOPMENT.md
```

### Namespaces
- Production: `SquidIT\PhpCodingStandards\PHPStan\Rules\...`
- Support: `SquidIT\PhpCodingStandards\PHPStan\Support\...`
- Tests (per your mapping rule):
  - `SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\...`
  - `SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\...`

---

## PHPStan rule identifiers (v1)
All MUST start with `squidit.naming*`:

- `squidit.namingTypeSuffixMismatch`
- `squidit.namingInterfaceBareNameNotice`
- `squidit.namingIterablePluralMismatch`
- `squidit.namingMapForbidden`
- `squidit.namingForeachValueVarMismatch`
- `squidit.namingLoggerContextKeyCamelCase`
- `squidit.namingEnumBackedValueCamelCase`
- `squidit.namingNoServiceInstantiation`

---

## Optional “run on demand” wiring (non-PHP)
> You said composer scripts aren’t the question; this is just a concrete suggestion.

- `composer cs:naming:check` → runs PHPStan with `phpstan-naming.neon`

---

# PR Plan (1 PR = 1 development session)

Each PR includes:
- ✅ Deliverables (files to add/modify)
- ✅ Acceptance checklist
- ✅ Test fixtures to add

---

## PR 1 — Scaffold + CI-ready baseline

### Deliverables
- Project wiring and minimal docs.
- Rule test harness wired (empty tests ok).

### Files
- `composer.json` (autoload psr-4 for `SquidIT\PhpCodingStandards\`)
- `phpunit.xml`
- `phpstan-naming.neon` (loads extension services)
- `README.md` (how to run the experimental rules)
- `DEVELOPMENT.md` (this plan)

### Acceptance checklist
- `vendor/bin/phpunit` runs.
- `vendor/bin/phpstan analyse -c phpstan-naming.neon` loads configuration and exits cleanly (even with no rules registered yet or with stub registration).

### Fixtures
- None required.

---

## PR 2 — Support: naming normalization + plural/singular engine

### Deliverables
- Implement support classes (no rules yet).
- Unit tests for support classes.

### Files
- `src/PHPStan/Support/NameNormalizer.php`
- `src/PHPStan/Support/Pluralizer.php`
- `src/PHPStan/Support/Singularizer.php`
- `tests/Unit/PHPStan/Support/NameNormalizerTest.php`
- `tests/Unit/PHPStan/Support/PluralizerTest.php`
- `tests/Unit/PHPStan/Support/SingularizerTest.php`

### What to cover (support behaviors)
- Use **short class names only**.
- Strip suffixes: `Interface|Abstract|Trait`.
- Allow stripped variants for `Dto|Vo|Entity` (case-insensitive).
- **Never strip**: `Collection|Factory`.
- CamelCase normalisation for initialisms.
- Plural allowed forms: `*s`, `*List`, `*Collection`, `*ById`, `*ByKey`, `*Lookup`.
- Singularization removes `List|Collection|Lookup|ById|ByKey` then de-pluralizes.

### Acceptance checklist
- Support tests cover edge cases:
  - `UserDto` → base allows `user` and `userDto`
  - `SelectableChannelInterface` → base allows `selectableChannel` and `channel` (hierarchy handled later)
  - Forbid stripping `Factory`/`Collection`

### Fixtures
- None (pure unit tests).

---

## PR 3 — Support: type candidate resolver + variable matcher

### Deliverables
- Resolve “allowed base names” from PHPStan `Type`:
  - Ignore `null` and `false` in unions.
  - Only named object types.
  - Expand hierarchy but exclude **internal/builtin** candidates entirely.
- Variable matching:
  - exact match OR endsWith(ucfirst(base)).
  - interface bare-name triggers “notice” decision (rule will emit later).

### Files
- `src/PHPStan/Support/TypeCandidateResolver.php`
- `src/PHPStan/Support/VariableNameMatcher.php`
- `src/PHPStan/Support/DenyList.php` (optional simple value object)
- `tests/Unit/PHPStan/Support/VariableNameMatcherTest.php`
- `tests/Unit/PHPStan/Support/TypeCandidateResolverTest.php`

### Acceptance checklist
- Unit tests validate:
  - `Foo|null|false` → candidates only `foo`.
  - Hierarchy expansion returns parent/interface names (non-internal only).
  - Internal/builtin candidates are excluded from the suffix set.

### Fixtures
- Use small test doubles or PHPStan testing utilities; avoid project-wide fixtures here.

---

## PR 4 — Rule: Type suffix mismatch (+ interface bare-name notice)

### Deliverables
- Rule checks:
  - Assignments (`$x = expr`)
  - Properties (`private Foo $bar`)
  - Promoted properties
- Expressions covered:
  - `new`, `clone`
  - calls where inferred return is object (including static constructors per return-type, not called-class)
- Emits:
  - hard error: `squidit.namingTypeSuffixMismatch`
  - notice: `squidit.namingInterfaceBareNameNotice` when inferred is interface and var name is bare base

### Files
- `src/PHPStan/Rules/Naming/TypeSuffixMismatchRule.php`
- `tests/Unit/PHPStan/Rules/Naming/TypeSuffixMismatchRuleTest.php`
- `tests/Fixtures/Naming/type-suffix-mismatch.php`

### Fixture content to include (scenarios)
- ✅ `private Foo $foo;` passes
- ❌ `private Foo $bar;` fails (expects `foo` suffix)
- ✅ `private Foo $barFoo;` passes
- ✅ `private ChannelInterface $channel;` passes but emits **notice**
- ✅ `private ChannelInterface $inboxChannel;` passes
- ✅ union return `Foo|Bar|null` allows suffix `Foo` or `Bar`
- ✅ `clone $foo` assignment passes when suffix matches

### Acceptance checklist
- Rule is stateless (no node storage).
- Messages include:
  - expected suffix options
  - inferred type(s) considered

---

## PR 5 — Rule: Iterable plural naming + Map forbidden

### Deliverables
- Detect iterable-of-objects using PHPStan inferred iterable value type and PHPDoc generics.
- Enforce allowed variable/property names for collections:
  - `nodes`, `nodeList`, `nodeCollection`, `nodeById`, `nodeByKey`, `nodeLookup` + prefixed variants.
- Hard error if name contains `map` in any case:
  - identifier `squidit.namingMapForbidden`

### Files
- `src/PHPStan/Rules/Naming/IterablePluralNamingRule.php`
- `tests/Unit/PHPStan/Rules/Naming/IterablePluralNamingRuleTest.php`
- `tests/Fixtures/Naming/iterable-plural.php`
- (optional) `tests/Fixtures/Naming/map-forbidden.php`

### Fixture scenarios
- `/** @var array<int, Node> */ $nodes = ...;` ✅
- `$nodeList` ✅
- `$activeNodeList` ✅
- `$nodeById` ✅
- `$nodeMap` ❌ `squidit.namingMapForbidden`
- associative `array<string, Node>` still prefers plural forms; allow `nodeByKey` ✅

### Acceptance checklist
- No reliance on “string contains List” alone; must confirm iterable value type is object.
- Map forbidden check applies regardless of type (per your “hard error” requirement).

---

## PR 6 — Rule: Foreach element variable naming

### Deliverables
- Enforce foreach value-var naming:
  - Allowed: singularized iterable var name
  - Allowed: element type suffix
  - Allowed: singular+type combined
  - Prefixing allowed via “endsWith base” rule
- Ignore key-var naming in v1.
- Skip destructuring.

### Files
- `src/PHPStan/Rules/Naming/ForeachValueVariableNamingRule.php`
- `tests/Unit/PHPStan/Rules/Naming/ForeachValueVariableNamingRuleTest.php`
- `tests/Fixtures/Naming/foreach-value-var.php`

### Fixture scenarios
- `$children as $child` ✅ (singular of `$children`)
- `$children as $childNode` ✅ (singular + type)
- `$children as $firstChildNode` ✅ (prefix + combined)
- `$children as $item` ❌ mismatch
- `$nodeList as $node` ✅ (List→singular)
- `$nodeById as $node` ✅ (ById→singular)

### Acceptance checklist
- If iterable var name can’t be singularized, fall back to type suffix enforcement.
- Must use inferred element type candidates (object only).

---

## PR 7 — Rule: Logger context keys camelCase (scoped)

### Deliverables
- Only enforce for method calls where receiver is `Psr\Log\LoggerInterface`.
- Only inspect context argument (2nd arg).
- Only enforce for **string-literal keys**.
- Rule rejects snake_case (contains `_`).

### Files
- `src/PHPStan/Rules/Naming/LoggerContextKeyCamelCaseRule.php`
- `tests/Unit/PHPStan/Rules/Naming/LoggerContextKeyCamelCaseRuleTest.php`
- `tests/Fixtures/Logger/logger-context-keys.php`

### Fixture scenarios
- `$logger->info('x', ['fooBar' => 1]);` ✅
- `$logger->info('x', ['foo_bar' => 1]);` ❌
- `$logger->info('x', [$key => 1]);` ✅ (skipped)
- `$notLogger->info('x', ['foo_bar' => 1]);` ✅ (out of scope)

### Acceptance checklist
- No “array literal anywhere” enforcement yet (explicitly scoped to logger calls).

---

## PR 8 — Rule: Enum backed values camelCase + `to*()` exception

### Deliverables
- For backed enums with string backed values:
  - camelCase string literal is OK
  - snake_case is error unless enum contains a `to*()` method that references the exact literal string.
- Keep scanning local to that enum node (no global indexing).

### Files
- `src/PHPStan/Rules/Naming/EnumBackedValueCamelCaseRule.php`
- `tests/Unit/PHPStan/Rules/Naming/EnumBackedValueCamelCaseRuleTest.php`
- `tests/Fixtures/Enum/enum-backed-values.php`

### Fixture scenarios
- `case Foo = 'fooBar';` ✅
- `case Foo = 'foo_bar';` ❌ without conversion
- `case Foo = 'foo_bar';` ✅ with `toDb()` returning `'foo_bar'`
- `case Foo = 'foo_bar';` ✅ if any `to*()` method references `'foo_bar'` anywhere in body

### Acceptance checklist
- Method name matcher: `to[A-Z].*` (simple)
- Literal matching is exact string value match

---

## PR 9 — Rule: No instantiation of non-VO/DTO inside non-VO/DTO (Factory exception)

### Deliverables
- When encountering `new ClassName(...)` inside a class method (including `__construct`):
  - allowed if containing class short name ends with `Factory`
  - allowed if instantiated class is internal/builtin
  - allowed if instantiated class is classified as VO/DTO
  - otherwise error: “Inject or use a factory”
- VO/DTO structural classifier (cached):
  - readonly class AND public API limited to getters/pure helpers (allowlist)
  - class with readonly and only public properties counts as VO/DTO
  - public methods like `equals/toArray/jsonSerialize/__toString` allowed
  - private/protected methods allowed

### Files
- `src/PHPStan/Support/VoDtoClassifier.php`
- `src/PHPStan/Support/ContainingClassResolver.php`
- `src/PHPStan/Rules/Architecture/NoServiceInstantiationRule.php`
- `tests/Unit/PHPStan/Rules/Architecture/NoServiceInstantiationRuleTest.php`
- `tests/Fixtures/Architecture/no-service-instantiation.php`
- `tests/Fixtures/Architecture/vo-dto-examples.php`

### Fixture scenarios
- In non-factory class: `new HttpClient()` ❌
- In `FooFactory`: `new HttpClient()` ✅
- `new DateTimeImmutable()` ✅
- `new UserDto(...)` readonly, promoted props ✅
- `new SomethingReadonlyWithHandle()` ❌ (public behavior method not in allowlist)

### Acceptance checklist
- No cross-file analysis needed.
- Classifier uses reflection info via PHPStan scope, cached by FQCN.

---

## PR 10 — Docs + rollout + tuning playbook

### Deliverables
- Document all rules, identifiers, examples, suppression patterns.
- Provide baseline workflow guidance for existing repos.
- Provide “tuning knobs”:
  - deny-list updates
  - VO/DTO method allowlist adjustments
  - pluralization exceptions

### Files
- `README.md` expanded
- `DEVELOPMENT.md` updated with real-world tuning notes
- `docs/identifiers.md` (optional)
- `docs/suppression.md` (optional)

### Acceptance checklist
- Someone can run:
  - `phpstan -c phpstan-naming.neon`
  - interpret any error
  - choose: fix vs ignore-by-identifier vs `@phpstan-ignore-next-line`

---

# Test Fixture Matrix (what we must cover)

| Rule / Identifier | Fixture file(s)                                          | Must cover |
|---|----------------------------------------------------------|---|
| `squidit.namingTypeSuffixMismatch` | `tests/Fixtures/Naming/TypeSuffixMismatch.php`           | assign + property + promoted prop; new/clone/call return type; union ignoring null/false |
| `squidit.namingInterfaceBareNameNotice` | same as above                                            | bare interface name emits notice, prefixed interface name passes |
| `squidit.namingIterablePluralMismatch` | `tests/Fixtures/Naming/IterablePlural.php`               | list + assoc arrays; allowed suffixes; prefixes |
| `squidit.namingMapForbidden` | `tests/Fixtures/Naming/mapForbidden.php` (or bundled)    | any var/property containing Map/map triggers |
| `squidit.namingForeachValueVarMismatch` | `tests/Fixtures/Naming/foreachValueVar.php`              | singularized iterable name + element type suffix + combined; fallback behavior |
| `squidit.namingLoggerContextKeyCamelCase` | `tests/Fixtures/Logger/loggerContextKeys.php`            | only LoggerInterface; only context arg; only string-literal keys |
| `squidit.namingEnumBackedValueCamelCase` | `tests/Fixtures/Enum/enumBackedValues.php`               | camel ok; snake error; snake ok with `to*()` referencing literal |
| `squidit.namingNoServiceInstantiation` | `tests/Fixtures/Architecture/noNerviceInstantiation.php` | non-factory fails; factory ok; internal ok; VO/DTO ok; readonly behavior fails |

---

# “Definition of Done” (overall)
- All rules registered in `phpstan-naming.neon`.
- Every identifier has at least 3 fixture scenarios:
  1) valid
  2) invalid
  3) edge case (union/hierarchy/prefix/exemption)
- No rule stores AST nodes or Types in long-lived structures (only small string/boolean caches).
- Error messages include:
  - variable/property name
  - inferred type(s)
  - allowed suffix candidates
  - identifier
  - (for instantiation rule) “Inject or use a factory” guidance.

---
