# Prompt — Create a development plan

> Replace every `{{PLACEHOLDER}}` before sending. Delete any optional section you do not need.

## Read first

```
AGENTS.md
vendor/squidit/php-coding-standards/README.md
{{ADDITIONAL_CONTEXT_FILES}}
```

`AGENTS.md` chains to the company-wide agent instructions. Read the standards README so you understand the
automated checks the resulting code must pass — a plan that proposes names the analyser rejects is a plan
that gets rewritten during implementation.

## Goal

Produce a development plan detailed enough to hand to a developer with no prior knowledge of this work.

Write the plan to `{{OUTPUT_PATH}}`.

## What we want to develop

{{DESCRIBE_THE_FEATURE_OR_CHANGE}}

{{OPTIONAL: current behaviour as you understand it — ask the agent to confirm or correct it before planning}}

## Plan requirements

- **A full directory file tree** of every file created or modified.
- **Interfaces written out completely.** This is how we steer naming before any implementation exists.
- **Implementation proposals as snippets only.** Do not write the implementation.
- **Test details** — which tests are added or changed, and what each one proves.
- **State what the plan does not cover**, so scope is explicit rather than assumed.

## Module structure

New modules follow this layout:

| Path | Contains |
|---|---|
| `ModuleName/Data` | Value objects and DTOs |
| `ModuleName/Interface` | Interfaces forming the public API |
| `ModuleName/Enum` | Enums |
| `ModuleName/Factory` | Factories |
| `ModuleName/Factory/Interface` | Factory interfaces, kept out of the public API entry directory |
| `ModuleName/SubModuleName/*` | Repeat the above, or ask for clarification |

## Naming

All naming follows the style guide in the company-wide `AGENT.md` — descriptive names, no abbreviations.
Class-suffix meaning is enforced by static analysis, so a suffix is a claim about behaviour, not decoration.

{{OPTIONAL: repository-specific naming rules or a conventions document to follow}}

## Rules of engagement

- **Ask clarification questions before writing the plan** when anything is unclear or underspecified.
- If the request rests on an incorrect understanding of the current code, say so and explain the actual
  behaviour before proposing anything.
- Do not modify code in this session. This is a planning session.
