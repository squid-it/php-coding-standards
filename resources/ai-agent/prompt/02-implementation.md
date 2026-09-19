# Prompt — Implement a documented plan

> Replace every `{{PLACEHOLDER}}` before sending. Delete any optional section you do not need.

## Read first

```
AGENTS.md
vendor/squidit/php-coding-standards/README.md
{{PLAN_FILES_TO_IMPLEMENT}}
```

Read the standards README **before** implementing, so you understand the automated review the code must
survive. Fixing a naming-rule violation after the fact means renaming every variable of that type too.

## Task

Implement the plan exactly as documented.

{{OPTIONAL: restrict to a stage or section, e.g. "Stages 1 to 3 only"}}

## Deviation protocol

If you encounter a needed change or a deviation from the plan, **discuss it before implementing it.**
Once agreed, follow it and note it in the report.

A plan that turns out to be wrong is useful information. Silently implementing something different is not.

## Implementation rules

- **Add validation where it is needed to make the change safe — but do not double validate.** If the plan
  already describes a validation point, do not add a second one somewhere else.
- Do not widen scope beyond the plan. Note anything worth doing separately instead of doing it.
- Each logical step should leave the test suite green.

## Tests

- **Do not use fixtures unless absolutely required** — use mocks and stubs.
- Do not use a PHPDoc template name as a variable name. Template names usually contain a `T` and the
  analyser will flag the collision.
- {{OPTIONAL: coverage or test-suite expectations}}

## Verification

When done, run:

```
{{VERIFICATION_COMMAND}}
```

The default for repositories using these standards is `composer fix` — code-style fixes, PHPStan analysis,
then unit tests with coverage. Check `composer.json` for what this repository actually defines rather than
assuming a script exists.

A failing naming check is the safety net working, not an obstacle. Fix the cause, do not suppress the rule.

## Report

Write a report to `{{REPORT_OUTPUT_PATH}}` containing:

- every file created, modified or deleted, and why;
- every deviation from the plan, and what was agreed;
- verification output — state plainly if anything still fails;
- anything found along the way that belongs in a follow-up rather than this change.

Then ask me to review the work.
