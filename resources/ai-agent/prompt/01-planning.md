# Prompt — Create a development plan

> Replace every `{{PLACEHOLDER}}` before sending. Delete any optional section you do not need.

## Read first

```
AGENTS.md
vendor/squidit/php-coding-standards/resources/ai-agent/docs/planning.md
vendor/squidit/php-coding-standards/resources/ai-agent/docs/architecture.md
vendor/squidit/php-coding-standards/resources/ai-agent/docs/writing-documents.md
{{ADDITIONAL_CONTEXT_FILES}}
```

`AGENTS.md` chains to the company-wide agent instructions. Also read every topic instruction file listed there whose
description matches the planned work. Consult `vendor/squidit/php-coding-standards/README.md` only when a check reports
an error you do not know how to fix, or when a rule's behavior is unclear.

## Task

Produce a development plan that meets the requirements in the company-wide `planning.md`.

Write the plan to `{{OUTPUT_PATH}}`.

## What we want to develop

{{DESCRIBE_THE_FEATURE_OR_CHANGE}}

{{OPTIONAL: current behaviour as you understand it — ask the agent to confirm or correct it before planning}}

## Naming

{{OPTIONAL: repository-specific naming rules or a conventions document to follow}}

## Rules of engagement

- Do not modify code in this session. This is a planning session.
