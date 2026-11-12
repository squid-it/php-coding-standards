---
name: planning
description: 'Requirements for development plans: file tree, responsibility map, interfaces, caller example, refactor ownership and preserved behavior, tests, and scope. Read when creating or revising a development plan, including a refactor plan, whether or not a planning prompt is used.'
---

# Development plans

These rules supplement the company-wide `AGENTS.md`. They apply to every development plan, whether the developer asks
for one directly or a session starts from the prompts in
`vendor/squidit/php-coding-standards/resources/ai-agent/prompt/`. Judge the design against
`vendor/squidit/php-coding-standards/resources/ai-agent/docs/architecture.md`, and write the plan following
`vendor/squidit/php-coding-standards/resources/ai-agent/docs/writing-documents.md`. Also read every topic instruction
file listed in the company-wide `AGENTS.md` whose description matches the planned work.

A plan is detailed enough to hand to a developer with no prior knowledge of the work.

## Plan requirements

- **A full directory file tree** of every file created or modified, including tests. New modules follow the layout
  under "Module structure" in `architecture.md`.
- **A responsibility map**: for every class the plan creates or changes, its responsibility, its reason to change, and
  what it must not do, following "Single responsibility" in `architecture.md`. When a class needs two reasons to change,
  propose the split in the plan, not during implementation.
- **Interfaces written out completely.** This is how naming is steered before any implementation exists.
- **Usage**: one representative caller showing the configuration, how the service is obtained (container resolution
  or construction), and the result. State which inputs the caller supplies. For a public API change, show the usage
  before and after the change, and list the compatibility changes.
- **Implementation proposals as snippets only.** Do not write the implementation.
- **Test details**: which tests are added or changed, and what each one proves.
- **Scope**: what the plan does not cover, so scope is explicit rather than assumed.

Include these when they apply:

- **Refactors:** a before-and-after ownership table that maps the affected behavior to its current and proposed owner,
  including what stays in the original class. List the observable behavior that must not change: results, ordering,
  side effects, exceptions, when validation runs, and lifecycle or cache behavior. List intentional behavior and API
  changes separately, and agree on them before the plan is finalized. Tie tests and acceptance criteria to both lists.
- **Validation that is added, moved, or removed:** a table with each check, its owner, and when it runs, following
  "Validation ownership" in `architecture.md`.
- **A proposed factory:** the consumer, the point where it creates fresh instances, and why the DI container cannot
  provide the required lifetime, following "Object construction roles" in `architecture.md`.
- **File paths or platform-specific behavior:** how the code handles Windows and Linux, and the path test cases from
  "Path handling tests" in `phpunit.md`.
- **Existing classes that differ from the module layout:** the agreed structural scope, following "Module structure"
  in `architecture.md`.

## Naming

All naming follows the style guide in the company-wide `AGENTS.md`: descriptive names, no abbreviations. Check the
proposed names against the naming rules and the enforced PHPStan rules there; a plan that proposes names the analyser
rejects gets rewritten during implementation. Class-suffix meaning is enforced by static analysis, so a suffix is a
claim about behavior, not decoration.

## Rules of engagement

- Resolve routine inputs, such as the change description, the affected code, and existing plans, from the conversation
  and the repository. Ask about the remaining uncertainty as described under "Development Workflow" in the
  company-wide `AGENTS.md`.
- If the request rests on an incorrect understanding of the current code, say so and explain the actual behavior before
  proposing anything.
- Settle design choices before writing the plan. A plan contains no open alternatives unless the developer agreed to
  leave that item open (see "Settled content" in `writing-documents.md`).
- A direct plan request does not require the development folder that `00-start-plan.md` sets up. Do not invent chunk
  plans, reports, or other planning documents that do not exist.
- Planning does not authorize code changes. Writing the plan file follows the file-change approval rules in the
  company-wide `AGENTS.md`.
