---
name: writing-documents
description: 'Rules for writing and revising Markdown documents: plans, proposals, specifications, reviews, reports, change requests, registers, and READMEs. Covers document order, keeping only the current state, settled content, and recording verification results. Read before writing a document or revising one, including a second round.'
---

# Writing documents

These rules supplement the company-wide `AGENTS.md`. They apply to every Markdown document an agent writes or revises.
Points raised in a document follow "Documenting and discussing points" in the company-wide `AGENTS.md`.

The goal is a document that a developer without prior context can open, understand from its first lines, and trust as
a description of the current state.

## Three kinds of document

- **Specifications** describe the current or target state: plans, proposals, specifications, change requests, guides,
  READMEs, and ad-hoc reviews such as a review of a proposal. Revise them in place.
- **Registers** hold the current state of a list of items, such as an open-issue register or a status overview. Revise
  each entry in place; remove or move an entry when it is settled.
- **Tracking documents** record what happened over time: delivery reports, review reports in a development folder, and
  changelogs. Add a new section for each round. Do not rewrite earlier entries, and do not repeat them: a follow-up
  records only what changed, such as what was resolved, what is still open, and what is new.

## Order

Start every document so that a reader without context knows what it is about before any status or detail:

1. **Title.**
2. **Introduction**, in a few sentences: what this document is, its subject (module, repository, or rule set), and who
   it is for.
3. **Problem**, when the document proposes a change: what is wrong or missing today.
4. **Goal**: what is true when the work is done. Include non-goals: things that could reasonably be goals but are
   deliberately excluded.
5. **Status**, when the document needs one: one line, such as "Agreed, not implemented". Use a status table only when
   parts of the work have different states.
6. **The body.**
7. **Decisions**, when alternatives are worth keeping: per decision, the alternatives and why they were rejected.

Nothing comes before the introduction, including a status line. Leave out sections that do not apply; a README or a
guide usually has no problem or status. Write each decision into the section it changes. The Decisions section keeps
only the alternatives and reasons; do not add a decision table or amendments at the top.

## Current state only

Specifications and registers describe the state as it is. When something changes, rewrite the affected text and
delete what no longer applies. A reader must not need earlier versions or the conversation to understand the document.

- Do not describe history or the process that produced the document. Words that signal it: "previously",
  "originally", "now", "new", "currently", "no longer", "as of this writing", "the earlier proposal", "this session",
  "the developer confirmed".
- Keep an earlier state only when a reader needs it:
  - in a migration section with before and after, when callers must change their code or behavior changes for them;
  - as a rejected alternative under Decisions, when it would otherwise be proposed again.
- State the status once, for the whole document. Mark only the parts whose status differs; do not label every section
  "agreed".
- State each fact in one place. When the same decision appears in a table, the body, and an example, one change leaves
  the others contradicting it.
- Stay on the subject. Material about another subject belongs in its own document, with one link to it.

## Settled content

A document records verified facts and settled decisions. Keep what is built and verified apart from what is agreed but
not yet built; an agreed design is not evidence that its implementation or tests pass.

- Resolve design and implementation choices in the conversation before writing them down. A document contains no open
  questions, unresolved alternatives, candidate contracts, or decisions left to the implementer, unless the developer
  explicitly agreed to leave that particular item open. Calling the document a draft or an outline, or a request to
  write it, is not that agreement.
- An agreed open item goes in the open-issue register when the work has one, and otherwise in an "Open items" section
  at the end. Record the item, why it is open, who decides it, by when, and which work depends on it.
- When a decision changes, update every place it affects in the same revision: text, examples, diagrams, tables, and
  the file inventory.

## Verification results

Record each verification command with one result: Passed, Failed, Skipped, or Pending, and name the operating system
it ran on. A passing command gets only "Passed": no test, assertion, or skip counts, no runtime or memory use, and no
copied output.

When a failure remains because it is not fixed in this work, record the number of failing tests, the names of the
failing tests when a reader needs them to find the failure, and the reason when known.

## Before handing over

Re-read the whole document, not only the changed section, and check that:

- the introduction says what the document is about, and the goal follows it;
- no stale text, history, or superseded alternative remains;
- text, examples, diagrams, and tables do not contradict each other;
- no decision is left to the implementer without the developer's agreement;
- each fact appears once.

Bring a missing decision back to the developer in the conversation; do not invent agreement.

## Form

- Refer to sections, issues, and decisions by name, never by number. Write methods as `ClassName::method()`.
- Use a table to compare options or before-and-after states, and a fenced code block with a language for code.
- Use a concrete example when an abstract statement could be read in more than one way.
