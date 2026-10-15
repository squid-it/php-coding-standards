# Delivery report guide

Create a separate report for each implemented chunk, named after its chunk plan file (`NN-<slug>.md`). Link the report
from the overview only after it exists.

Include:

1. The chunk plan and the implementation date.
2. Every created, modified, or deleted file and why it changed.
3. Completed behaviour and tests, including any deliberate temporary limitation.
4. Every class the chunk touched, checked against its row in the responsibility map. Name any row that changed and the
   agreement that changed it.
5. Deviations from the chunk or general plan and the agreement that authorised each one.
6. The exact verification commands ({{AGENT_VERIFICATION}}; {{DEVELOPER_VERIFICATION}}), each with a Passed, Failed,
   Skipped, or Pending result. Record the failure reason when a check fails. Do not infer one platform's result from
   another.
7. Follow-ups outside this chunk, and any known failure.
8. Every open issue raised at kickoff, its chosen resolution or documented deferral, mitigation, and revisit point.

Do not record test or assertion counts, skip totals, runtime, memory use, or copied PHPUnit progress output.

The delivery report is evidence for review and is not rewritten afterwards. A correction after review is added as a new
section ("Review follow-up"). Keep the overview to status and links.

The agent must not create a Git commit. After the developer approves the review, remind the developer to commit this
delivery manually. Record the developer's commit confirmation in the review report; do not infer it from a clean
working tree.
