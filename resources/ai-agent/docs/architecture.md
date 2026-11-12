---
name: architecture
description: 'Design and architecture rules for SOLID, single responsibility, validation ownership, parameter limits, module layout, dependencies, configuration grouping, object construction roles, and invokable objects. Read for any planning, refactoring, or review; when creating a class or interface; when adding a method, property, or constructor dependency to an existing class; when constructing objects or choosing a class suffix (`Factory`, `Builder`, `Instantiator`, `Provider`); or when wiring DI.'
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
class, judge the class as it will be after the change. After extracting collaborators in a refactor, judge the class
that remains in the same way: an extraction does not by itself give the remaining class a single responsibility.

- State the class's responsibility in one sentence and check that sentence against what the class contains: its groups
  of private methods, its state, and its collaborators. A sentence that needs "and" is a warning sign. A sentence
  without "and" proves nothing when it is broad: "loads settings" can hide configuration and discovery rules.
- Name the class's reason to change. A class that changes for two unrelated reasons, for example a lifecycle rule and a
  configuration format, has two responsibilities.
- Warning signs that a class has taken on a second responsibility:
  - it decides what should happen and also carries out the mechanics behind that decision;
  - it validates a rule it does not own (see "Validation ownership");
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

## Validation ownership

Assign each check to the component that has the information to enforce it:

- A data object validates its own fields.
- The object that holds a collection validates the relationships within that collection, such as duplicate entries.
- The component that performs an external operation (file, network, process) checks the state that operation needs,
  when the operation runs.

An orchestrator that calls a collaborator's check in the required order is not validating: it owns the order and the
failure behavior, not the rule. A rule about the relationship between several objects does not belong to one of those
objects merely because it mentions that object's data.

## Constructor & Method Parameter Limits

- **Avoid Parameter Bloat:** Having more than 8 constructor or method parameters on service, orchestrator, or manager
  classes is a code smell indicating a violation of the Single Responsibility Principle (SRP).
- **Exemption for Value Objects:** Readonly data-transfer/value objects (living in the `Data` subdirectory) are exempt
  from this limit when representing structured payloads or configuration snapshots.
- **Remediation Strategy:** When a service constructor exceeds 8 arguments:
  1. Group related scalar/configuration arguments into an immutable Value Object in the `Data` directory.
  2. Move pre-flight validation into the value object that carries the data, or into a named static constructor
     (`from{X}()`). Move one-time assembly into the DI container registration (see "Object construction roles").
  3. Evaluate whether the class is taking on too many responsibilities and should be split into smaller, focused
     collaborators.
  4. When in doubt, ask for clarification.

## Module structure

New modules follow this layout:

| Path | Contains |
|---|---|
| `ModuleName/Data` | Value objects and DTOs |
| `ModuleName/Interface` | Interfaces forming the public API |
| `ModuleName/Enum` | Enums |
| `ModuleName/Exception` | Exceptions |
| `ModuleName/Factory` | Factories |
| `ModuleName/Factory/Interface` | Factory interfaces, kept out of the public API entry directory |
| `ModuleName/SubModuleName/*` | Repeat the above, or ask for clarification |

When a refactor touches existing classes that differ from this layout and the request does not settle whether to align
them, recommend aligning them or leaving them as they are. State the consequences: namespace and public API changes,
and files moved outside the classes being refactored. Agree on the structural scope before finalizing the plan;
approval to refactor does not cover a wider reorganization.

## Dependencies

- Prefer constructor injection for object dependencies and depend on interfaces when a useful interface exists.
- A promoted constructor dependency may use a default `new` expression when the class has a sensible default
  implementation.
- State owned exclusively by the object does not need to be injected.
- Let the repository's DI container assemble services that are created once for the application or a container scope,
  using its existing registrations.
- When a class must create multiple dependency instances during execution, inject a factory.
- Group configuration values that describe one concept into one value object, whatever the parameter count. Keep
  service dependencies as constructor arguments; never move them into an options object to lower the count.

## Object construction roles

- A class ending with `Factory` creates fresh instances for a consumer during normal application execution. Use one only
  when the current use case needs repeated creation; name the consumer and the point where it creates instances. Expose
  a named creation method such as `create()`.
- One-time assembly of a service belongs in its DI container registration (a service provider or an `Instantiator`),
  even when the wiring is complex. Centralizing construction, convenience in tests, and possible future callers are not
  a creation need. Do not add a builder or other wrapper to hold wiring that the container can own.
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
