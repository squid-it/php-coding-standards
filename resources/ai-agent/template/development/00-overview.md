# {{MODULE_NAME}} implementation overview

**Current state ({{DATE}}):** {{ONE_LINE: where the work stands now, not how it got there}}

The folder structure and how to start a session are in the [README](README.md). The design is in the
[general plan](plan.md).

## Next step

{{ONE_OR_TWO_SENTENCES: the next action and who takes it. Keep this section to the next action; what happened before
belongs in the delivery and review reports.}}

## Progress

| # | Chunk plan | Depends on | Status | Delivery | Review |
|---|---|---|---|---|---|
| 01 | [{{CHUNK_TITLE}}](01-{{CHUNK_SLUG}}.md) | — | Planned | — | — |
| {{NN}} | {{ONE_ROW_PER_PROPOSED_CHUNK}} | {{DEPENDENCIES}} | Planned | — | — |

Status values: Planned, Ready for implementation, Implementing, Awaiting verification, Ready for review, Changes
requested, Awaiting manual commit, Complete, Dropped. This table is the only place a chunk's status is kept, and it
holds only status and links. Verification output belongs in the delivery and review reports. A chunk without a plan
file links to nothing until its plan exists.

{{OPTIONAL: for each side chunk (letter suffix), one sentence on why it sits where it sits and what depends on it}}

## How we work

1. **Read first.** Before planning, implementing, or reviewing PHP, read `AGENTS.md`, the installed company-wide
   `vendor/squidit/php-coding-standards/resources/ai-agent/AGENT.md`, and the coding standards README. The installed
   [planning](../../vendor/squidit/php-coding-standards/resources/ai-agent/prompt/01-planning.md),
   [implementation](../../vendor/squidit/php-coding-standards/resources/ai-agent/prompt/02-implementation.md), and
   [review](../../vendor/squidit/php-coding-standards/resources/ai-agent/prompt/03-review.md) prompts govern those
   sessions; [start-chunk.md](start-chunk.md) maps their placeholders.
2. **Discuss open issues before code.** At the start of a chunk, state each relevant open issue in plain words: what
   fails, at least two practical options, and a recommendation. Decisions that change the public API, a safety
   guarantee, or the test architecture are settled before implementation and written into the general plan and the
   open issues.
3. **Explicit approval.** Agreeing with a plan or asking to start a chunk is not approval to change files. Implement
   one chunk at a time, only after the developer explicitly approves it, then stop for review.
4. **One responsibility per class.** Every class has one row in the responsibility map in the general plan. A change
   that would give a class a second responsibility is raised as a deviation, with a proposed split, before code is
   written. A review reads every touched class as a whole, not only the diff.
5. **Placeholders fail loudly.** An unimplemented public method throws a `BadMethodCallException` naming the method
   and the chunk that delivers it. It never returns as if the work was done.
6. **Verification.** The implementation session runs {{AGENT_VERIFICATION}}. The developer runs
   {{DEVELOPER_VERIFICATION}}. Record each command and whether it passed, failed, was skipped, or is pending, with the
   failure reason; no test or assertion counts. Results from one platform never stand in for another.
7. **Reports.** The implementation session writes `delivery/<chunk plan filename>` (see the
   [delivery guide](delivery/README.md)); the review session writes `review/<chunk plan filename>` (see the
   [review guide](review/README.md)). Link each from the progress table.
8. **Commits.** The agent never creates a Git commit; read-only Git commands are fine. After the developer approves
   the review, mark the chunk Awaiting manual commit and remind the developer to commit. Mark it Complete when the
   developer confirms. Do not begin the next chunk automatically.
9. **Writing docs.** Every document starts with a one-line current state. Decisions are written into the section they
   change, not added as amendments at the top. Refer to sections, issues, and decisions by name, never by number, and
   to methods as `ClassName::method()`.

## Review focus per chunk

{{OPTIONAL: per chunk, what its review must look at beyond the standard review prompt}}
