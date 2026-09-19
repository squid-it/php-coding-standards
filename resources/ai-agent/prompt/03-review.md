# Prompt — Review implemented work

> Replace every `{{PLACEHOLDER}}` before sending. Delete any optional section you do not need.

## Read first

```
AGENTS.md
vendor/squidit/php-coding-standards/README.md
{{FILES_OR_PLAN_UNDER_REVIEW}}
```

{{OPTIONAL: the plan the implementation was supposed to follow, so the review can check it against intent}}

## Task

Do a thorough review. Look for bugs, errors and incorrect logic.

Where you see simplification opportunities, point them out — but keep them separate from defects. A
simplification is a suggestion; a defect is not.

## What to check

- **Correctness** — wrong logic, unhandled failure paths, incorrect assumptions about state or lifetime.
- **Whether it matches the plan** — silent deviations, and anything specified but not built.
- **Test quality** — does each test prove what it claims, and would it fail if the code were wrong?
- **Standards** — naming and architectural rules from the company-wide `AGENT.md` and the enforced PHPStan
  rules.
- {{OPTIONAL: concurrency, coroutine lifetime, resource ownership, or other domain-specific concerns}}

## How to report

For every finding, state:

1. **Where** — `file:line`.
2. **What is wrong** — one sentence.
3. **How it fails** — concrete inputs or state leading to the wrong outcome. A finding with no failure
   path is a suggestion, not a defect; label it as such.

Verify findings against the code before reporting them. A confident wrong finding costs more than a missed
one, because it gets acted on.

If you find nothing in a category, say so explicitly rather than staying silent.

## Rules of engagement

- **Do not change code in this session** unless I ask for it. Report first.
- Ask clarification questions when intended behaviour is ambiguous — do not guess and then review against
  the guess.

## Output

Write the review to `{{REVIEW_OUTPUT_PATH}}`.
