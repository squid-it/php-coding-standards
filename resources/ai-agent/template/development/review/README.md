# Review report guide

Create a separate report when a chunk is reviewed, named after its chunk plan file (`NN-<slug>.md`). Link it from the
overview after it exists.

Use the installed `03-review.md` prompt. Record:

- the reviewed delivery report, and the commit or diff;
- the environment the review ran in ({{REVIEW_ENVIRONMENT, for example PHP and extension versions, platform}});
- the exact verification commands ({{REVIEW_VERIFICATION}}) with their results, recorded as described under
  "Verification results" in the company-wide `writing-documents.md`;
- findings with `file:line`, a short description of that location, and the Intended, Observed, and Resolution parts,
  with the failure path under Observed;
- responsibility findings under their own heading: every touched class read as a whole and checked against the
  responsibility map, with the responsibility that does not belong and its proposed home;
- suggestions, kept separate from findings;
- the developer's review decision.

State explicitly when a category has no finding.

Check that the chunk's open issues were raised before implementation and that any deferral has a concrete mitigation
and revisit point. Do not treat a documented risk as a passing test.

For the final chunk, run the full verification described in the general plan. A chunk remains pending until its
required checks and review are complete. A repeated review is added as a new section ("Review follow-up"); earlier
findings are not rewritten, and the follow-up records only what changed: what was resolved, what is still open, and
what is new.

After the developer approves the review, ask the developer to commit the chunk manually. The agent must never run
`git commit`. The overview stays at Awaiting manual commit until the developer confirms the commit.
