---
name: architecture
description: 'Design and architecture rules for SOLID, single responsibility, parameter limits, dependencies, object construction roles, and invokable objects. Read for any planning or review; when creating a class or interface; when adding a method, property, or constructor dependency to an existing class; when constructing objects or choosing a class suffix (`Factory`, `Builder`, `Instantiator`, `Provider`); or when wiring DI.'
---

# Design and Architecture Standards

These rules supplement the company-wide `AGENTS.md`.

## SOLID and simplicity

- Honor SOLID principles where they improve cohesion, dependency direction, testability, or maintainability.
- We must honor the Single Responsibility Principle (SRP) and the Dependency Inversion Principle (DIP).
- Keep the design simple and proportionate to the current requirement. Do not over-engineer or introduce abstractions for
  speculative future needs.
- Do not add an interface, factory, builder, instantiator, provider, or other architectural layer merely to demonstrate a
  design principle or bypass an automated rule.

## Single responsibility

Single responsibility is judged on the whole class, not on the change. Each addition can look reasonable on its own
while the class it lands in slowly takes on a second job. Before adding a method, property, or dependency to an existing
class, judge the class as it will be after the change.

- State the class's responsibility in one sentence without "and". If the sentence needs "and", the class has two
  responsibilities.
- Name the class's reason to change. A class that changes for two unrelated reasons, for example a lifecycle rule and a
  configuration format, has two responsibilities.
- Warning signs that a class has taken on a second responsibility:
  - it decides what should happen and also carries out the mechanics behind that decision;
  - it validates the contents of another object; validation belongs to the object that carries the data;
  - a group of its private methods serves a different subject than its public methods;
  - its decisions can only be tested with real infrastructure (processes, sockets, the file system) because the
    mechanics cannot be replaced by a stub;
  - its constructor approaches the parameter limit below.
- "Keep the design simple" never justifies growing an existing class past its responsibility. One focused collaborator
  is simpler than one class doing two jobs. The effort of agreeing a new name, folder, and wiring is not a reason to put
  the code in the class that already holds the state.
- When a change would give a class a second responsibility, stop and propose the split before writing the code: the new
  collaborator's name, its single responsibility, and its interface. Do not split silently, and do not grow the class
  silently.

## Constructor & Method Parameter Limits

- **Avoid Parameter Bloat:** Having more than 8 constructor or method parameters on service, orchestrator, or manager
  classes is a code smell indicating a violation of the Single Responsibility Principle (SRP).
- **Exemption for Value Objects:** Readonly data-transfer/value objects (living in the `Data` subdirectory) are exempt
  from this limit when representing structured payloads or configuration snapshots.
- **Remediation Strategy:** When a service constructor exceeds 8 arguments:
  1. Group related scalar/configuration arguments into an immutable Value Object in the `Data` directory.
  2. Move dependency extraction, pre-flight validation, or assembly logic into a dedicated Factory, Builder, or named
     static constructor (`from{X}()`, `create()`).
  3. Evaluate whether the class is taking on too many responsibilities and should be split into smaller, focused
     collaborators.
  4. When in doubt, ask for clarification.

## Dependencies

- Prefer constructor injection for object dependencies and depend on interfaces when a useful interface exists.
- A promoted constructor dependency may use a default `new` expression when the class has a sensible default
  implementation.
- State owned exclusively by the object does not need to be injected.
- When a class must create multiple dependency instances during execution, inject a factory.

## Object construction roles

- A class ending with `Factory` creates fresh instances during normal application execution. Use a factory when new
  instances must be constructed repeatedly or when construction policy needs to be centralized. Expose a named creation
  method such as `create()`.
- A class ending with `Builder` is a helper for assembling a complex object or object graph from multiple collaborating
  objects. Use a builder only when that construction would otherwise be difficult to understand. Expose a named method
  such as `build()`.
- A class ending with `Instantiator` is exclusively an invokable DI-container definition that resolves or constructs one
  registered service. It may receive container-resolved dependencies through `__invoke()` and must not be used as a
  general application factory.
- The `Provider` suffix is reserved for DI service providers implementing
  `League\Container\ServiceProvider\ServiceProviderInterface`, directly or indirectly. This includes classes extending
  `League\Container\ServiceProvider\AbstractServiceProvider` or a project-specific subclass of it.
- The restriction on instantiating service classes with `new`, and on taking one of these suffixes to gain that
  permission, is in the company-wide `AGENTS.md` under "Design and Architecture Standards".

## Invokable objects

- Declare `__invoke()` only when instances are intentionally passed to an API or boundary that expects a callable, such as
  a DI instantiator, coroutine task, or callback.
- Do not use `__invoke()` as a replacement for a factory's `create()` method, a builder's `build()` method, or a
  descriptive domain method.
- When an API expects a callable, supply an object implementing `__invoke()` (see the company-wide `AGENTS.md`).
