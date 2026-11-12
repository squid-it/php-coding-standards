---
name: review
description: 'Rules for reviewing code, plans, and delivered work: what to check, how to verify findings, and how to report them. Read when asked for a review, when writing a review document, or when responding to review findings.'
---

# Reviewing work

These rules supplement the company-wide `AGENTS.md`. Judge design against
`vendor/squidit/php-coding-standards/resources/ai-agent/docs/architecture.md`. A review written as a document also
follows `vendor/squidit/php-coding-standards/resources/ai-agent/docs/writing-documents.md`.

## What to check

- **Correctness:** wrong logic, unhandled failure paths, incorrect assumptions about state or lifetime.
- **Match with the plan:** silent deviations, and anything specified but not built.
- **Test quality:** does each test prove what it claims, and would it fail if the code were wrong?
- **Single responsibility:** read every class the change touches as a whole, not only the diff. Check it against the
  warning signs under "Single responsibility" in `architecture.md` and, when the work has a plan, against its row in
  the plan's responsibility map. A class that has taken on a second responsibility is a finding even when every changed
  line is correct.
- **Refactors:** compare the ownership the plan claims with the complete resulting classes, especially the original
  class after collaborators were extracted. Check that the behavior the plan lists as preserved still holds, and that
  every intentional behavior or API change was agreed.
- **New abstractions:** for each new interface, factory, builder, or other layer, name the responsibility it separates
  or the replacement or testing need it serves. A layer without one is a finding.
- **Plans and other documents:** check them against `writing-documents.md`: the introduction says what the document is
  about, only settled decisions are recorded, and open items exist only where the developer agreed to them. Check a
  plan also against the requirements in `planning.md`.
- **Standards:** naming rules from the company-wide `AGENTS.md`, architecture rules from `architecture.md`, and the
  enforced PHPStan rules.

## How to report

- Start each finding with its location (`file:line`) and a short description of that location, then give the Intended,
  Observed, and Resolution parts defined under "Documenting and discussing points" in the company-wide `AGENTS.md`.
- Under Observed, give the concrete input or state that leads to the wrong outcome. A finding with no failure path is a
  suggestion, not a defect; label it as such.
- Report defects, responsibility findings, and suggestions under separate headings. For a responsibility finding, name
  the responsibility that does not belong under Observed and its proposed home under Resolution, so it is not mistaken
  for an optional suggestion.
- Report simplification opportunities as suggestions.
- Verify every finding against the code before reporting it. A confident wrong finding costs more than a missed one,
  because it gets acted on.
- State explicitly when a category has no finding.

## Rules of engagement

- Do not change code during a review unless asked. Report first.
- When intended behaviour is ambiguous, ask instead of reviewing against a guess.
