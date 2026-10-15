# {{MODULE_NAME}}: general plan

**Current state ({{DATE}}):** {{ONE_LINE, for example: Outline; the public API and responsibility map are agreed,
internal design is open.}} Chunk order and status: [overview](00-overview.md).

This document describes the finished module. The chunk plans describe how it is built; every decision they make is
written into this plan, in the section it belongs to. For parts that are already built, the code is the specification:
this plan keeps only what the code does not say (why, and what later chunks still change). Parts still to be built keep
their full design here.

Package: `{{PACKAGE_NAME}}` · Namespace: `{{NAMESPACE}}` · PHP {{PHP_VERSION}} · {{PLATFORM_AND_RUNTIME_REQUIREMENTS}}

### Classes used in this document

Methods are written as `ClassName::method()`, also for instance methods. Module classes are named by their short class
name; their namespace follows from the directory layout under "Public API".

{{OPTIONAL: a table of short names for external classes this plan mentions often}}

| Short name | Class |
|---|---|
| {{SHORT_NAME}} | {{FULLY_QUALIFIED_CLASS_NAME}} |

---

## Problem

{{What is wrong or missing today, in plain words. Who notices, and what it costs them.}}

## Goal

{{What is true when the module is finished. A short list of outcomes, each one checkable.}}

## Solution in one paragraph

{{The design in one paragraph: the main parts, who decides, who does the mechanics, and how they talk to each other.}}

## Scope

### In scope

{{What this module delivers.}}

### Out of scope

{{What it deliberately does not deliver, and where that work goes instead.}}

## Preconditions

{{Extensions, library versions, platforms, configuration, or other modules that must exist first.}}

## Public API

### Directory layout

{{The full file tree of every file created or modified, following the module structure in the planning prompt
(`Data`, `Interface`, `Enum`, `Factory`, `Factory/Interface`, sub-modules). Include the tests.}}

### Responsibility map

One row per class. The responsibility is one sentence without "and". A class that does not fit in one row is two
classes. Implementation and review are checked against this table; a change to a row is a decision, written here first.

| Class | Responsibility | Reason to change | Must not |
|---|---|---|---|
| {{CLASS_NAME}} | {{ONE_SENTENCE}} | {{THE_ONE_REASON_THIS_CLASS_CHANGES}} | {{WHAT_IT_MUST_NOT_DO, for example "log", "name ForkProcess", "validate options"}} |

{{OPTIONAL: exclusive ownership stated as rules, for example "`ChildProcesses` is the only class that names
`ForkProcess`", "only the manager logs".}}

### Type specifications

{{Every interface written out completely, with PHPDoc. Value objects and enums with their fields and cases. This is
where naming is steered before any implementation exists.}}

### Usage

{{How a consumer uses the module: a minimal example first, then the variations.}}

## Internal design

{{The behaviour behind the public API: states, invariants, the main flows, failure handling. In an outline, list each
open design question here with the chunk that must answer it, instead of guessing.}}

## Tests

### Test conventions

{{Repository test rules that matter here: mocks and stubs only in unit tests, real objects in integration tests,
where scratch files go, platform-dependent skips.}}

### Unit tests

{{Per test class: what it proves.}}

### Integration tests

{{Per test class: what it proves, and on which platform it runs.}}

## Acceptance criteria

{{Checkable statements. The final chunk verifies every one.}}

## Decisions

{{Each decision by name: what was decided, the alternatives, and why. May be empty in an outline.}}

## Risks and follow-ups

{{Known risks with their mitigation, and work deliberately left for later. May be empty in an outline.}}
