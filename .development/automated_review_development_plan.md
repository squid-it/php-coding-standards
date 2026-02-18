# SquidIT PHPStan Automated Review Rules - Development Plan

This plan is split into small sessions and keeps runtime overhead low by using stateless, per-node rule checks with small string/boolean caches.

## Goals

- Enforce naming and architectural conventions that support automated review.
- Keep new rules experimental and optional via a separate config file.
- Minimize false positives through scoped checks and clear rule identifiers.

## Non-goals (v1)

- Destructuring assignment naming.
- Catch variable naming.
- Global project collectors or pre-indexing passes.
- Enforcing camelCase for all array keys everywhere (scoped to `LoggerInterface` context only in v1).

## Agreed repository conventions

- Keep plan documents in `.development/`.
- Keep PHPUnit config as `phpunit.xml.dist`.
- Use grouped rules structure:
  - `src/PHPStan/Rules/Naming/`
  - `src/PHPStan/Rules/Architecture/`
  - `src/PHPStan/Rules/Restrictions/`
- Fixtures must mirror the same category layout under:
  - `tests/Unit/PHPStan/Rules/Fixtures/Naming/`
  - `tests/Unit/PHPStan/Rules/Fixtures/Architecture/`
  - `tests/Unit/PHPStan/Rules/Fixtures/Restrictions/`
- Use strict PascalCase for fixture folder and file names.
- `phpstan-autoreview.neon` includes only the new experimental autoreview rules.

## Target structure

```text
src/PHPStan/Rules/
  Naming/
  Architecture/
  Restrictions/
src/PHPStan/Support/
tests/Unit/PHPStan/Rules/
  Naming/
  Architecture/
  Restrictions/
  Fixtures/
    Naming/
    Architecture/
    Restrictions/
tests/Unit/PHPStan/Support/
phpstan.neon
phpstan-autoreview.neon
phpunit.xml.dist
.development/automated_review_development_plan.md
.development/automated_review_development_plan_pr_split.md
```

## Namespace and identifier strategy

- Namespace root: `SquidIT\PhpCodingStandards\PHPStan\Rules`
- Support namespace: `SquidIT\PhpCodingStandards\PHPStan\Support`
- Identifier taxonomy:
  - Naming rules: `squidit.naming.<ruleName>`
  - Architecture rules: `squidit.architecture.<ruleName>`
  - Restrictions rules: `squidit.restrictions.<ruleName>`

## Shared helpers (build once, reuse everywhere)

### 1) `NameNormalizer`

Deterministic behavior:
- Use short class name only.
- Normalize to camelCase base from class short name.
- Mandatory strip suffixes: `Interface`, `Abstract`, `Trait`.
  - Only stripped form is kept for these suffixes.
- Optional strip suffixes: `Dto`, `Vo`, `Entity`.
  - Keep both unstripped and stripped forms.
- Never strip suffixes: `Factory`, `Collection`.
  - Keep only unstripped form.

Examples:
- `ChannelInterface` -> `channel`
- `UserDto` -> `userDto`, `user`
- `OrderEntity` -> `orderEntity`, `order`
- `UserFactory` -> `userFactory`
- `NodeCollection` -> `nodeCollection`

### 2) `TypeCandidateResolver`

Deterministic behavior:
- Resolve candidate names from inferred PHPStan type.
- Ignore `null` and `false` from unions.
- Use named object types only.
- Expand hierarchy (self, parents, interfaces).
- Exclude internal/builtin candidates from naming candidates.

Boundary for internal/builtin:
- Internal/builtin means reflection reports symbol as internal (`ReflectionClass::isInternal()` true).
- Includes PHP core symbols, SPL symbols, and extension-provided symbols.
- During hierarchy expansion, include only userland classes/interfaces for candidate generation.

### 3) `VariableNameMatcher`

- Valid if variable equals base name.
- Valid if variable ends with `ucfirst(baseName)`.
- Surface interface bare-name signal for notice-level reporting.

### 4) `Pluralizer` and `Singularizer`

- Plural rules: `company -> companies`, `class -> classes`, default `+s`.
- Singularization strips `List`, `Collection`, `Lookup`, `ById`, `ByKey`, then de-pluralizes.

### 5) `VoDtoClassifier`

Deterministic behavior with two gates:

Immutability gate (must pass one):
- Class is `readonly`, or
- All declared non-static instance properties are `readonly`.

Public API gate (must pass):
- Allowed public methods only:
  - `__construct`
  - `get*`, `is*`, `has*`
  - `toArray`, `jsonSerialize`, `__toString`, `equals`, `equalsTo`
- Any other public method fails classification.
- Private/protected methods are ignored for classification.

Other rules:
- Internal/builtin classes are automatically allowed for instantiation rule checks.
- Cache classification by FQCN.

### 6) `ContainingClassResolver`

- Resolve containing class for a node scope.
- Provide `Factory` exception signal when class name ends with `Factory`.

## Rules list (v1)

### Rule 1: Type suffix mismatch

Identifier(s):
- `squidit.naming.typeSuffixMismatch`
- `squidit.naming.interfaceBareNameNotice`

Applies to assignments, typed properties, promoted properties.

### Rule 2: Iterable plural naming + map forbidden

Identifier(s):
- `squidit.naming.iterablePluralMismatch`
- `squidit.naming.mapForbidden`

Applies to object iterables in assignments and properties.

### Rule 3: Foreach value variable naming

Identifier:
- `squidit.naming.foreachValueVarMismatch`

Applies to foreach value variable naming, with singular/type fallback logic.

### Rule 4: Logger context key camelCase

Identifier:
- `squidit.naming.loggerContextKeyCamelCase`

Applies only to string literal keys in context argument on `Psr\Log\LoggerInterface` calls.

### Rule 5: Enum backed value camelCase

Identifier:
- `squidit.naming.enumBackedValueCamelCase`

Backed string values must be camelCase unless a `to*()` method references that exact literal.

### Rule 6: No service instantiation in non-factory classes

Identifier:
- `squidit.architecture.noServiceInstantiation`

Disallow `new` for non-VO/DTO services in non-factory classes.

## Memory and performance guardrails

- No global collectors or project-wide registries.
- Cache only scalar metadata:
  - `fqcn -> allowedBaseNames`
  - `fqcn -> voDtoClassification`
- Never cache AST nodes.
- Use early exits for unsupported nodes/types.

## Session dependency chain

- Session 1 has no dependency.
- Session 2 depends on Session 1.
- Session 3 depends on Session 2.
- Session 4 depends on Sessions 2 and 3.
- Session 5 depends on Sessions 2 and 3.
- Session 6 depends on Sessions 2, 3, and 5.
- Session 7 depends on Sessions 2 and 3.
- Session 8 depends on Session 1.
- Session 9 depends on Sessions 2 and 3.
- Session 10 depends on Sessions 4 through 9.

## Development sessions

## Session 1 - Align current codebase and isolate autoreview config

Status:
- Completed (approved).

Deliverables:
- Move existing rules into grouped folders:
  - `SingleClassPerFileRule` -> `Rules/Architecture`
  - `DisallowAnonymousFunctionRule` -> `Rules/Restrictions`
  - `DisallowLogicalNotRule` -> `Rules/Restrictions`
- Move corresponding test classes into matching grouped test folders.
- Move existing fixtures to mirrored category paths:
  - `Fixtures/Architecture/SingleClassPerFile/...`
  - `Fixtures/Restrictions/DisallowAnonymousFunction/...`
  - `Fixtures/Restrictions/DisallowLogicalNot/...`
- Normalize all fixture names to strict PascalCase.
- Update existing rule identifiers to taxonomy:
  - `squidit.architecture.singleClassPerFile`
  - `squidit.restrictions.disallowAnonymousFunction`
  - `squidit.restrictions.disallowLogicalNot`
- Add `phpstan-autoreview.neon` and keep it isolated to new experimental rules only.
- Update `phpstan.neon` and `README.md` namespaces, identifiers, and paths after moves.

Acceptance:
- `composer test:unit` passes with no behavior regressions.
- `composer analyse` passes.
- `vendor/bin/phpstan analyse -c phpstan-autoreview.neon` loads cleanly.

## Session 2 - Core support utilities

Status:
- Completed (approved).

Deliverables:
- Implement `NameNormalizer`, `Pluralizer`, `Singularizer` exactly as specified.
- Add unit tests in `tests/Unit/PHPStan/Support`.

Acceptance:
- Support utilities are fully unit tested, including suffix and pluralization edge cases.

## Session 3 - Type resolution and variable matching

Status:
- Completed (approved).

Deliverables:
- Implement `TypeCandidateResolver` and `VariableNameMatcher` exactly as specified.
- Add deny-list plumbing for future tuning.

Acceptance:
- Tests cover unions (`Foo|null|false`), hierarchy expansion, and internal/builtin boundaries.

## Session 4 - Rule 1 implementation

Status:
- Implemented (pending review).

Deliverables:
- Add `TypeSuffixMismatchRule` in `src/PHPStan/Rules/Naming`.
- Add `TypeSuffixMismatchRuleTest` in `tests/Unit/PHPStan/Rules/Naming`.
- Add fixtures in:
  - `tests/Unit/PHPStan/Rules/Fixtures/Naming/TypeSuffixMismatch/Valid`
  - `tests/Unit/PHPStan/Rules/Fixtures/Naming/TypeSuffixMismatch/Invalid`

Acceptance:
- Fixtures validate property, promoted property, assignment, clone, and union scenarios.

## Session 5 - Rule 2 implementation

Deliverables:
- Add `IterablePluralNamingRule` in `src/PHPStan/Rules/Naming`.
- Add matching test and fixtures under:
  - `tests/Unit/PHPStan/Rules/Fixtures/Naming/IterablePluralNaming`

Acceptance:
- Plural naming enforced for object iterables.
- `Map` naming violation always reported.

## Session 6 - Rule 3 implementation

Deliverables:
- Add `ForeachValueVariableNamingRule` in `src/PHPStan/Rules/Naming`.
- Add matching test and fixtures under:
  - `tests/Unit/PHPStan/Rules/Fixtures/Naming/ForeachValueVariableNaming`

Acceptance:
- Singularized iterable-name and type-based naming are both validated.

## Session 7 - Rule 4 implementation

Deliverables:
- Add `LoggerContextKeyCamelCaseRule` in `src/PHPStan/Rules/Naming`.
- Add matching test and fixtures under:
  - `tests/Unit/PHPStan/Rules/Fixtures/Naming/LoggerContextKeyCamelCase`

Acceptance:
- Only logger context arg string-literal keys are checked.

## Session 8 - Rule 5 implementation

Deliverables:
- Add `EnumBackedValueCamelCaseRule` in `src/PHPStan/Rules/Naming`.
- Add matching test and fixtures under:
  - `tests/Unit/PHPStan/Rules/Fixtures/Naming/EnumBackedValueCamelCase`

Acceptance:
- `to*()` literal-reference exception is covered.

## Session 9 - Rule 6 implementation

Deliverables:
- Add `NoServiceInstantiationRule` in `src/PHPStan/Rules/Architecture`.
- Add `VoDtoClassifier` and `ContainingClassResolver` in `src/PHPStan/Support`.
- Add matching test and fixtures under:
  - `tests/Unit/PHPStan/Rules/Fixtures/Architecture/NoServiceInstantiation`

Acceptance:
- Factory exception, builtin exception, VO/DTO allowance, and invalid service instantiation are covered.

## Session 10 - Documentation and rollout

Deliverables:
- Document all identifiers and examples in `README.md`.
- Document tuning guidance and suppression strategy.
- Document `phpstan-autoreview.neon` usage as optional experimental checks.

Acceptance:
- A contributor can run:
  - `phpstan analyse -c phpstan.neon` for stable rules.
  - `phpstan analyse -c phpstan-autoreview.neon` for experimental autoreview rules.

## Identifier catalog

Stable existing rules after taxonomy migration:
- `squidit.architecture.singleClassPerFile`
- `squidit.restrictions.disallowAnonymousFunction`
- `squidit.restrictions.disallowLogicalNot`

New experimental autoreview rules:
- `squidit.naming.typeSuffixMismatch`
- `squidit.naming.interfaceBareNameNotice`
- `squidit.naming.iterablePluralMismatch`
- `squidit.naming.mapForbidden`
- `squidit.naming.foreachValueVarMismatch`
- `squidit.naming.loggerContextKeyCamelCase`
- `squidit.naming.enumBackedValueCamelCase`
- `squidit.architecture.noServiceInstantiation`
