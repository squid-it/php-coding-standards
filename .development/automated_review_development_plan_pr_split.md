# SquidIT PHPStan Automated Review Ruleset (Experimental) - Repo-Ready PR Checklist

Concrete PR-by-PR build checklist with file targets and fixture matrix.

## Repository layout (target)

```text
src/
  PHPStan/
    Rules/
      Naming/
      Architecture/
      Restrictions/
    Support/
tests/
  Unit/
    PHPStan/
      Rules/
        Naming/
        Architecture/
        Restrictions/
        Fixtures/
          Naming/
          Architecture/
          Restrictions/
      Support/
phpstan.neon
phpstan-autoreview.neon
phpunit.xml.dist
composer.json
README.md
.development/automated_review_development_plan.md
.development/automated_review_development_plan_pr_split.md
```

## Namespace targets

- Production rules: `SquidIT\PhpCodingStandards\PHPStan\Rules\...`
- Support: `SquidIT\PhpCodingStandards\PHPStan\Support\...`
- Tests:
  - `SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\...`
  - `SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\...`

## Fixture convention (locked)

- Fixture root: `tests/Unit/PHPStan/Rules/Fixtures`.
- Fixture categories mirror `src/PHPStan/Rules` categories.
- Required layout:
  - `tests/Unit/PHPStan/Rules/Fixtures/Naming/<RuleName>/Valid/<ScenarioName>.php`
  - `tests/Unit/PHPStan/Rules/Fixtures/Naming/<RuleName>/Invalid/<ScenarioName>.php`
  - `tests/Unit/PHPStan/Rules/Fixtures/Architecture/<RuleName>/Valid/<ScenarioName>.php`
  - `tests/Unit/PHPStan/Rules/Fixtures/Architecture/<RuleName>/Invalid/<ScenarioName>.php`
  - `tests/Unit/PHPStan/Rules/Fixtures/Restrictions/<RuleName>/Valid/<ScenarioName>.php`
  - `tests/Unit/PHPStan/Rules/Fixtures/Restrictions/<RuleName>/Invalid/<ScenarioName>.php`
- Folder names and file names are strict PascalCase.

## Optional experimental wiring

- `phpstan-autoreview.neon` is isolated and includes only new experimental autoreview rules.
- Existing stable rules remain in `phpstan.neon`.

## Identifier taxonomy (locked)

- Naming: `squidit.naming.<ruleName>`
- Architecture: `squidit.architecture.<ruleName>`
- Restrictions: `squidit.restrictions.<ruleName>`

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

## Boundary definitions (locked)

### NameNormalizer

- Use short class names only.
- Mandatory strip suffixes: `Interface`, `Abstract`, `Trait`.
  - Only stripped form is allowed.
- Optional strip suffixes: `Dto`, `Vo`, `Entity`.
  - Keep both stripped and unstripped forms.
- Never strip suffixes: `Factory`, `Collection`.
  - Keep only unstripped form.

Examples:
- `ChannelInterface` -> `channel`
- `UserDto` -> `userDto`, `user`
- `OrderEntity` -> `orderEntity`, `order`
- `UserFactory` -> `userFactory`
- `NodeCollection` -> `nodeCollection`

### TypeCandidateResolver internal/builtin boundary

- Ignore `null` and `false` in unions.
- Process named object types only.
- Expand self, parent, and interface hierarchy.
- Exclude any symbol where reflection reports internal (`ReflectionClass::isInternal()` true).
- Internal includes PHP core, SPL, and extension-provided symbols.
- Candidate list is generated from userland symbols only.

### VoDtoClassifier constraints

Immutability gate (must pass one):
- class is `readonly`, or
- all declared non-static instance properties are `readonly`.

Public API gate (must pass):
- allowed public methods:
  - `__construct`
  - `get*`, `is*`, `has*`
  - `toArray`, `jsonSerialize`, `__toString`, `equals`, `equalsTo`
- any other public method fails classification.
- private/protected methods are ignored.
- internal/builtin classes are auto-allowed for instantiation checks.

# PR Plan (1 PR = 1 session)

## PR 1 - Align existing project structure + baseline isolation

Depends on: none

### Deliverables

- Restructure existing rules into grouped folders (no behavior changes):
  - `SingleClassPerFileRule` -> `Rules/Architecture`
  - `DisallowAnonymousFunctionRule` -> `Rules/Restrictions`
  - `DisallowLogicalNotRule` -> `Rules/Restrictions`
- Move existing rule tests to grouped test folders.
- Move fixtures to mirrored category paths:
  - `Fixtures/Architecture/SingleClassPerFile/...`
  - `Fixtures/Restrictions/DisallowAnonymousFunction/...`
  - `Fixtures/Restrictions/DisallowLogicalNot/...`
- Normalize fixture names to strict PascalCase.
- Migrate existing rule identifiers to taxonomy.
- Add `phpstan-autoreview.neon` for experimental rules only.
- Update `phpstan.neon` and `README.md` references.

### Files

- Move `src/PHPStan/Rules/SingleClassPerFileRule.php` -> `src/PHPStan/Rules/Architecture/SingleClassPerFileRule.php`
- Move `src/PHPStan/Rules/DisallowAnonymousFunctionRule.php` -> `src/PHPStan/Rules/Restrictions/DisallowAnonymousFunctionRule.php`
- Move `src/PHPStan/Rules/DisallowLogicalNotRule.php` -> `src/PHPStan/Rules/Restrictions/DisallowLogicalNotRule.php`
- Move `tests/Unit/PHPStan/Rules/SingleClassPerFileRuleTest.php` -> `tests/Unit/PHPStan/Rules/Architecture/SingleClassPerFileRuleTest.php`
- Move `tests/Unit/PHPStan/Rules/DisallowAnonymousFunctionRuleTest.php` -> `tests/Unit/PHPStan/Rules/Restrictions/DisallowAnonymousFunctionRuleTest.php`
- Move `tests/Unit/PHPStan/Rules/DisallowLogicalNotRuleTest.php` -> `tests/Unit/PHPStan/Rules/Restrictions/DisallowLogicalNotRuleTest.php`
- Move `tests/Unit/PHPStan/Rules/Fixtures/SingleClassPerFile/*` -> `tests/Unit/PHPStan/Rules/Fixtures/Architecture/SingleClassPerFile/*`
- Move `tests/Unit/PHPStan/Rules/Fixtures/DisallowAnonymousFunction/*` -> `tests/Unit/PHPStan/Rules/Fixtures/Restrictions/DisallowAnonymousFunction/*`
- Move `tests/Unit/PHPStan/Rules/Fixtures/DisallowLogicalNot/*` -> `tests/Unit/PHPStan/Rules/Fixtures/Restrictions/DisallowLogicalNot/*`
- Add `phpstan-autoreview.neon`
- Update `phpstan.neon`
- Update `README.md`

### Acceptance checklist

- `vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit` passes.
- `vendor/bin/phpstan analyse -c phpstan.neon` passes.
- `vendor/bin/phpstan analyse -c phpstan-autoreview.neon` loads cleanly.

## PR 2 - Support: NameNormalizer + Pluralizer + Singularizer

Depends on: PR 1

### Deliverables

- Implement support classes.
- Add unit tests for support behaviors.
- Implement NameNormalizer exactly per locked boundary definitions.

### Files

- `src/PHPStan/Support/NameNormalizer.php`
- `src/PHPStan/Support/Pluralizer.php`
- `src/PHPStan/Support/Singularizer.php`
- `tests/Unit/PHPStan/Support/NameNormalizerTest.php`
- `tests/Unit/PHPStan/Support/PluralizerTest.php`
- `tests/Unit/PHPStan/Support/SingularizerTest.php`

### Acceptance checklist

- Unit tests cover mandatory/optional/never-strip suffix behavior.
- Unit tests cover initialism normalization and plural/singular edge cases.

## PR 3 - Support: TypeCandidateResolver + VariableNameMatcher

Depends on: PR 2

### Deliverables

- Implement type candidate extraction and variable matching.
- Add optional deny-list support object.
- Implement internal/builtin boundary exactly per locked definition.

### Files

- `src/PHPStan/Support/TypeCandidateResolver.php`
- `src/PHPStan/Support/VariableNameMatcher.php`
- `src/PHPStan/Support/DenyList.php` (optional)
- `tests/Unit/PHPStan/Support/TypeCandidateResolverTest.php`
- `tests/Unit/PHPStan/Support/VariableNameMatcherTest.php`

### Acceptance checklist

- Tests cover `Foo|null|false`, hierarchy expansion, and internal/builtin exclusion.

## PR 4 - Naming rule: TypeSuffixMismatch

Depends on: PR 2, PR 3

### Deliverables

- Implement assignment/property/promoted-property suffix checks.
- Add interface bare-name notice path.

### Files

- `src/PHPStan/Rules/Naming/TypeSuffixMismatchRule.php`
- `tests/Unit/PHPStan/Rules/Naming/TypeSuffixMismatchRuleTest.php`
- `tests/Unit/PHPStan/Rules/Fixtures/Naming/TypeSuffixMismatch/Valid/*.php`
- `tests/Unit/PHPStan/Rules/Fixtures/Naming/TypeSuffixMismatch/Invalid/*.php`

### Fixture scenarios

- `private Foo $Foo;` invalid and `private Foo $foo;` valid.
- `private Foo $barFoo;` valid.
- Interface bare-name notice case.
- Union and clone scenarios.

### Acceptance checklist

- Reports `squidit.naming.typeSuffixMismatch` and `squidit.naming.interfaceBareNameNotice` correctly.

## PR 5 - Naming rule: IterablePluralNaming

Depends on: PR 2, PR 3

### Deliverables

- Enforce plural naming for object iterables.
- Enforce map forbidden naming.

### Files

- `src/PHPStan/Rules/Naming/IterablePluralNamingRule.php`
- `tests/Unit/PHPStan/Rules/Naming/IterablePluralNamingRuleTest.php`
- `tests/Unit/PHPStan/Rules/Fixtures/Naming/IterablePluralNaming/Valid/*.php`
- `tests/Unit/PHPStan/Rules/Fixtures/Naming/IterablePluralNaming/Invalid/*.php`

### Fixture scenarios

- Valid: `nodes`, `nodeList`, `activeNodeList`, `nodeById`.
- Invalid: `nodeMap`.
- Assoc iterable case with valid naming.

### Acceptance checklist

- Reports `squidit.naming.iterablePluralMismatch` and `squidit.naming.mapForbidden` correctly.

## PR 6 - Naming rule: ForeachValueVariableNaming

Depends on: PR 2, PR 3, PR 5

### Deliverables

- Enforce foreach value variable naming by singularized iterable and/or element type.

### Files

- `src/PHPStan/Rules/Naming/ForeachValueVariableNamingRule.php`
- `tests/Unit/PHPStan/Rules/Naming/ForeachValueVariableNamingRuleTest.php`
- `tests/Unit/PHPStan/Rules/Fixtures/Naming/ForeachValueVariableNaming/Valid/*.php`
- `tests/Unit/PHPStan/Rules/Fixtures/Naming/ForeachValueVariableNaming/Invalid/*.php`

### Fixture scenarios

- Valid: `$children as $child`, `$children as $childNode`, `$children as $firstChildNode`.
- Invalid: `$children as $item`.

### Acceptance checklist

- Reports `squidit.naming.foreachValueVarMismatch` correctly.

## PR 7 - Naming rule: LoggerContextKeyCamelCase

Depends on: PR 2, PR 3

### Deliverables

- Enforce camelCase for string-literal context keys on logger calls only.

### Files

- `src/PHPStan/Rules/Naming/LoggerContextKeyCamelCaseRule.php`
- `tests/Unit/PHPStan/Rules/Naming/LoggerContextKeyCamelCaseRuleTest.php`
- `tests/Unit/PHPStan/Rules/Fixtures/Naming/LoggerContextKeyCamelCase/Valid/*.php`
- `tests/Unit/PHPStan/Rules/Fixtures/Naming/LoggerContextKeyCamelCase/Invalid/*.php`

### Fixture scenarios

- Valid: `['fooBar' => 1]`.
- Invalid: `['foo_bar' => 1]`.
- Skipped: dynamic key and non-logger receivers.

### Acceptance checklist

- Reports `squidit.naming.loggerContextKeyCamelCase` correctly.

## PR 8 - Naming rule: EnumBackedValueCamelCase

Depends on: PR 1

### Deliverables

- Enforce camelCase backed string values.
- Allow exception when `to*()` method references same literal.

### Files

- `src/PHPStan/Rules/Naming/EnumBackedValueCamelCaseRule.php`
- `tests/Unit/PHPStan/Rules/Naming/EnumBackedValueCamelCaseRuleTest.php`
- `tests/Unit/PHPStan/Rules/Fixtures/Naming/EnumBackedValueCamelCase/Valid/*.php`
- `tests/Unit/PHPStan/Rules/Fixtures/Naming/EnumBackedValueCamelCase/Invalid/*.php`

### Fixture scenarios

- Valid: `'fooBar'`.
- Invalid: `'foo_bar'` without conversion.
- Valid exception: `'foo_bar'` referenced from `toDb()` or other `to*()` method.

### Acceptance checklist

- Reports `squidit.naming.enumBackedValueCamelCase` correctly.

## PR 9 - Architecture rule: NoServiceInstantiation

Depends on: PR 2, PR 3

### Deliverables

- Implement non-factory service instantiation restriction.
- Add VO/DTO classifier and containing class resolver.
- Implement classifier exactly per locked boundary definitions.

### Files

- `src/PHPStan/Support/VoDtoClassifier.php`
- `src/PHPStan/Support/ContainingClassResolver.php`
- `src/PHPStan/Rules/Architecture/NoServiceInstantiationRule.php`
- `tests/Unit/PHPStan/Rules/Architecture/NoServiceInstantiationRuleTest.php`
- `tests/Unit/PHPStan/Rules/Fixtures/Architecture/NoServiceInstantiation/Valid/*.php`
- `tests/Unit/PHPStan/Rules/Fixtures/Architecture/NoServiceInstantiation/Invalid/*.php`

### Fixture scenarios

- Invalid in non-factory class: `new HttpClient()`.
- Valid in `*Factory` class.
- Valid internal/builtin class instantiation.
- Valid VO/DTO class and invalid readonly behavior class.

### Acceptance checklist

- Reports `squidit.architecture.noServiceInstantiation` correctly.

## PR 10 - Documentation, rollout, and tuning

Depends on: PR 4, PR 5, PR 6, PR 7, PR 8, PR 9

### Deliverables

- Expand `README.md` with all rule identifiers and examples.
- Document suppression strategy and tuning knobs.
- Document usage split:
  - stable: `phpstan.neon`
  - experimental: `phpstan-autoreview.neon`

### Files

- `README.md`
- `.development/automated_review_development_plan.md`
- `.development/automated_review_development_plan_pr_split.md`
- `docs/identifiers.md` (optional)
- `docs/suppression.md` (optional)

### Acceptance checklist

- Developers can run both configs and understand fix/suppress choices.

# Test fixture matrix

| Rule / Identifier | Fixture root | Must cover |
|---|---|---|
| `squidit.naming.typeSuffixMismatch` | `tests/Unit/PHPStan/Rules/Fixtures/Naming/TypeSuffixMismatch` | assign/property/promoted; new/clone/calls; union null/false handling |
| `squidit.naming.interfaceBareNameNotice` | `tests/Unit/PHPStan/Rules/Fixtures/Naming/TypeSuffixMismatch` | bare interface base notice and prefixed valid names |
| `squidit.naming.iterablePluralMismatch` | `tests/Unit/PHPStan/Rules/Fixtures/Naming/IterablePluralNaming` | list + assoc iterables; allowed suffixes; prefixes |
| `squidit.naming.mapForbidden` | `tests/Unit/PHPStan/Rules/Fixtures/Naming/IterablePluralNaming` | any variable/property containing `Map`/`map` |
| `squidit.naming.foreachValueVarMismatch` | `tests/Unit/PHPStan/Rules/Fixtures/Naming/ForeachValueVariableNaming` | singularized iterable + type suffix + combined fallback |
| `squidit.naming.loggerContextKeyCamelCase` | `tests/Unit/PHPStan/Rules/Fixtures/Naming/LoggerContextKeyCamelCase` | logger-only scope; context arg only; string-literal keys only |
| `squidit.naming.enumBackedValueCamelCase` | `tests/Unit/PHPStan/Rules/Fixtures/Naming/EnumBackedValueCamelCase` | camel valid; snake invalid; snake valid with `to*()` literal reference |
| `squidit.architecture.noServiceInstantiation` | `tests/Unit/PHPStan/Rules/Fixtures/Architecture/NoServiceInstantiation` | non-factory invalid; factory valid; builtin valid; VO/DTO valid; readonly behavior invalid |

# Definition of done

- Existing rules are grouped under `Architecture` and `Restrictions` without behavior change.
- Existing stable identifiers are migrated to taxonomy.
- New autoreview rules are grouped under `Naming` and `Architecture` as planned.
- `phpstan-autoreview.neon` includes only the new experimental rules.
- Every identifier has at least 3 fixture scenarios: valid, invalid, edge.
- No rule stores AST nodes in long-lived caches.
- Error messages include variable/property name, inferred type context, allowed candidates, and identifier.
