# {{MODULE_NAME}}: general plan

{{INTRODUCTION: two to four sentences: the module and its repository, what the module does, and who this plan is for.}}

This document describes the finished module. The chunk plans describe how it is built; every decision they make is
written into this plan, in the section it belongs to. For parts that are already built, the code is the specification:
this plan keeps only what the code does not say (why, and what later chunks still change). Parts still to be built keep
their full design here.

Package: `{{PACKAGE_NAME}}` · Namespace: `{{NAMESPACE}}` · PHP {{PHP_VERSION}} · {{PLATFORM_AND_RUNTIME_REQUIREMENTS}}

---

## Problem

{{What is wrong or missing today, in plain words. Who notices, and what it costs them.}}

## Goal

{{What is true when the module is finished. A short list of outcomes, each one checkable.}}

**Status ({{DATE}}):** {{ONE_LINE, for example: Outline; the public API and responsibility map are agreed.}} Chunk
order and status: [overview](00-overview.md).

## Classes used in this document

Methods are written as `ClassName::method()`, also for instance methods. Module classes are named by their short class
name; their namespace follows from the directory layout under "Public API".

{{OPTIONAL: a table of short names for external classes this plan mentions often}}

| Short name | Class |
|---|---|
| {{SHORT_NAME}} | {{FULLY_QUALIFIED_CLASS_NAME}} |

## Solution in one paragraph

{{The design in one paragraph: the main parts, who decides, who does the mechanics, and how they talk to each other.}}

## Scope

### In scope

{{What this module delivers.}}

### Out of scope

{{The non-goals: what could reasonably be expected from this module but is deliberately not delivered, and where that
work goes instead.}}

## Preconditions

{{Extensions, library versions, platforms, configuration, or other modules that must exist first.}}

## Public API

### Directory layout

{{The full file tree of every file created or modified, following the module structure in the company-wide
`architecture.md` (`Data`, `Interface`, `Enum`, `Exception`, `Factory`, `Factory/Interface`, sub-modules). Include
the tests.}}

### Responsibility map

One row per class, following "Single responsibility" in the company-wide `architecture.md`: one responsibility, checked
against the class's methods, state, and collaborators, and one reason to change. A class that needs two reasons to
change is two classes. Implementation and review are checked against this table; a change to a row is a decision,
written here first.

| Class | Responsibility | Reason to change | Must not |
|---|---|---|---|
| {{CLASS_NAME}} | {{ONE_SENTENCE}} | {{THE_ONE_REASON_THIS_CLASS_CHANGES}} | {{WHAT_IT_MUST_NOT_DO, for example "log", "name ForkProcess", "validate options"}} |

{{OPTIONAL: exclusive ownership stated as rules, for example "`ChildProcesses` is the only class that names
`ForkProcess`", "only the manager logs".}}

### Type specifications

{{Every interface written out completely, with PHPDoc. Value objects and enums with their fields and cases. This is
where naming is steered before any implementation exists.}}

### Usage

{{How a consumer uses the module: one representative caller first (the configuration, how the service is obtained,
and the result), then the variations. For a public API change: the usage before and after, with the compatibility
changes.}}

## Internal design

{{The behaviour behind the public API: states, invariants with their owner and when they are enforced, the main flows,
failure handling. A design question the developer agreed to leave open is registered in the open issues; name that
issue here instead of guessing.}}

{{OPTIONAL, for a refactor: the ownership before and after, including what stays in the original class; the behaviour
that must not change; and the intentional behaviour or API changes.}}

## Tests

### Test conventions

{{Repository test rules that matter here: mocks and stubs only in unit tests, real objects in integration tests,
where scratch files go, platform-dependent tests, and the Windows and Linux path cases from the company-wide
`phpunit.md`.}}

### Unit tests

{{Per test class: what it proves.}}

### Integration tests

{{Per test class: what it proves, and on which platform it runs.}}

## Acceptance criteria

{{Checkable statements. The final chunk verifies every one.}}

## Decisions

{{Per decision worth explaining, by name: the alternatives and why they were rejected. The decision itself is written
into the section it changes. May be empty.}}

## Risks and follow-ups

{{Known risks with their mitigation, and work deliberately left for later. May be empty in an outline.}}
