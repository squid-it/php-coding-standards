# {{MODULE_NAME}} open issues

**Current state ({{DATE}}):** {{ONE_LINE, for example: three issues are open; none is decided yet.}} Settled decisions
live in the [general plan](plan.md).

This register keeps unresolved choices and known limits visible. The [general plan](plan.md) describes the finished
behaviour; this file explains what can still go wrong and what was decided about it.

How to use it: the first chunk an issue affects raises it before implementation. Explain the failure in plain words,
give at least two practical options with a recommendation, and record the developer's decision here, in the issue's
own section. A file reference alone is not an explanation. An issue may be deferred when the chunk still delivers a
working, safe slice; the deferral states its impact, mitigation, and revisit point. A documented risk is not a passing
test.

| Issue | State | Revisit |
|---|---|---|
| [{{ISSUE_TITLE}}](#{{issue-anchor}}) | Open | {{CHUNK_OR_EVENT_THAT_MUST_DECIDE_IT}} |

States: Open, Deferred, Decided, Accepted limit. A decided issue keeps its section here when a limit remains; otherwise
its decision moves into the general plan and the row says where.

---

## Open issues

### {{ISSUE_TITLE}}

{{What can fail, in plain words: the input or state, and the wrong outcome.}}

- **Option A:** {{the option and its consequence}}
- **Option B:** {{the option and its consequence}}

**Recommendation:** {{which option and why}}

## Accepted limits

{{Issues decided as limits that remain, each with its decision and where it is documented for users. Empty until an
issue is accepted as a limit.}}
