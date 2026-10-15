# Chunk {{NN}}: {{CHUNK_TITLE}}

**Current state ({{DATE}}):** Planned. Depends on {{DEPENDENCIES, or "nothing"}}.

## Goal

{{What this chunk delivers and why it comes now. A working, testable slice that leaves the test suite green.}}

General plan sections: {{the sections of plan.md this chunk implements, by name}}.

## Responsibilities

{{The rows of the responsibility map this chunk creates or changes, copied from the general plan. For every existing
class this chunk adds code to: its responsibility sentence, and why the addition stays within it. If it does not, the
split belongs in this plan, not in the implementation.}}

| Class | Responsibility | Change in this chunk |
|---|---|---|
| {{CLASS_NAME}} | {{ONE_SENTENCE_FROM_THE_MAP}} | {{New, or what is added and why it fits}} |

## Design

{{Decisions specific to this chunk. A decision that changes the finished design is also written into the general plan,
in the section it belongs to.}}

## Open issues

{{The issues from 99-open-issues.md this chunk must raise before implementation, by name. "None" when there are none.}}

## Files

### Source

{{Every source file created, modified, or deleted.}}

### Tests

{{Every test file created, modified, or deleted.}}

### Documentation

{{Every documentation file changed, including this folder.}}

## Public API changes

{{Interfaces and value objects written out completely where they change. "None" when the public API does not change.}}

## Implementation proposals

{{Snippets only, for the parts where the approach is not obvious. Not the implementation.}}

## Tests

{{Which tests are added or changed, and what each one proves. A test that cannot fail when the code is wrong proves
nothing.}}

## Not covered

{{What this chunk deliberately leaves out, and which chunk or follow-up picks it up.}}

## Verification

{{The commands from "How we work" that apply to this chunk, plus any chunk-specific integration tests.}}
