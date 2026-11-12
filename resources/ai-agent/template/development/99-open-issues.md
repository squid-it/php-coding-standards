# {{MODULE_NAME}} open issues

This register keeps the unresolved choices and known limits of {{MODULE_NAME}} visible. The
[general plan](plan.md) describes the finished behaviour; this file explains what can still go wrong and what was
decided about it.

**Status ({{DATE}}):** {{ONE_LINE, for example: three issues are open; none is decided yet.}} Settled decisions live
in the [general plan](plan.md).

How to use it: an issue is registered here only when the developer agreed to leave it open. The first chunk an issue
affects raises it before implementation. State what is intended and what can fail in plain words, give at least two
practical options with a recommendation under Resolution, and record the developer's decision there, in the issue's
own section. A file reference alone is not an explanation. An issue may be deferred, with the developer's explicit
agreement, when the chunk still delivers a working, safe slice; the deferral states its impact, mitigation, and
revisit point. A documented risk is not a passing test.

| Issue | State | Revisit |
|---|---|---|
| [{{ISSUE_TITLE}}](#{{issue-anchor}}) | Open | {{CHUNK_OR_EVENT_THAT_MUST_DECIDE_IT}} |

States: Open, Deferred, Decided, Accepted limit. A decided issue keeps its section here when a limit remains; otherwise
its decision moves into the general plan and the row says where.

---

## Open issues

### {{ISSUE_TITLE}}

**Intended:** {{What should happen: the behaviour the plan or rule requires.}}

**Observed:** {{What can fail, in plain words: the input or state, and the wrong outcome.}}

**Resolution:** {{Open, or the agreed decision.}}

- **Option A:** {{the option and its consequence}}
- **Option B:** {{the option and its consequence}}

**Recommendation:** {{which option and why}}

## Accepted limits

{{Issues decided as limits that remain, each with its decision and where it is documented for users. Empty until an
issue is accepted as a limit.}}
