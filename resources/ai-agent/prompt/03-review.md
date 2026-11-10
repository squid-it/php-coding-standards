# Prompt — Review implemented work

> Replace every `{{PLACEHOLDER}}` before sending. Delete any optional section you do not need.

## Read first

```
AGENTS.md
vendor/squidit/php-coding-standards/resources/ai-agent/docs/review.md
vendor/squidit/php-coding-standards/resources/ai-agent/docs/architecture.md
{{FILES_OR_PLAN_UNDER_REVIEW}}
```

Also read every topic instruction file listed in the company-wide `AGENTS.md` whose description matches the files under
review. Consult `vendor/squidit/php-coding-standards/README.md` only when a check reports an error you do not know how
to fix, or when a rule's behavior is unclear.

{{OPTIONAL: the plan the implementation was supposed to follow, so the review can check it against intent}}

## Task

Do a thorough review. Look for bugs, errors and incorrect logic. Follow the company-wide `review.md` for what to check
and how to report each finding.

## Also check

- {{OPTIONAL: concurrency, coroutine lifetime, resource ownership, or other domain-specific concerns}}

## Output

Write the review to `{{REVIEW_OUTPUT_PATH}}`.
