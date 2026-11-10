# Start a {{MODULE_NAME}} chunk in a fresh session

Paste one of these messages into a new session (the [README](README.md) explains the folder). Replace `01` with the
chunk number from the [overview](00-overview.md), which also holds each chunk's status. The working rules (approval,
verification, reports, commits) are in the overview under "How we work".

## Write a chunk plan

> Read `.development/{{FEATURE_FOLDER}}/start-chunk.md` and follow its chunk-plan kickoff for chunk 01. Discuss the
> scope, the responsibilities, and the open issues first. Do not write the plan file until I explicitly approve. Do not
> change PHP files. Do not create a Git commit.

## Chunk-plan kickoff: what the agent does first

1. Read `AGENTS.md`, `vendor/squidit/php-coding-standards/resources/ai-agent/AGENTS.md`, and the vendor
   [planning prompt](../../vendor/squidit/php-coding-standards/resources/ai-agent/prompt/01-planning.md). Inspect the
   neighbouring code before designing a change.
2. Read the [overview](00-overview.md), the [general plan](plan.md), the [open issues](99-open-issues.md), the
   [chunk plan template](../../vendor/squidit/php-coding-standards/resources/ai-agent/template/development/chunk-plan.md),
   and the delivery and review reports of the chunks this chunk depends on.
3. In the first response: name the chunk, the general plan sections it implements, and the classes it creates or
   changes with their row in the responsibility map. For every existing class it adds to, say why the addition stays
   within that class's responsibility, or propose the split. Explain every relevant open issue in plain words (what can
   fail, at least two options, a recommendation).
4. Agree on the scope and any new names, then wait for the developer's **explicit approval to write the plan**.

Vendor prompt placeholders:

| Placeholder | Value |
|---|---|
| `{{ADDITIONAL_CONTEXT_FILES}}` | The general plan, the open issues, the chunk plan template, and the accepted dependency reports |
| `{{OUTPUT_PATH}}` | `.development/{{FEATURE_FOLDER}}/<NN>-<slug>.md` |
| Optional naming rules | The general plan's responsibility map and "Type specifications" |

After approval: write the chunk plan, add a decision that changes the finished design to the general plan, register
new open issues, and set the chunk's link and status (Ready for implementation) in the overview.

## Start an implementation session

> Read `.development/{{FEATURE_FOLDER}}/start-chunk.md` and follow its implementation kickoff for chunk 01. Confirm the
> chunk plan still matches the code and raise only new questions. Do not change files until I explicitly approve
> implementation of this chunk. Do not create a Git commit.

## Implementation kickoff: what the agent does first

1. Read `AGENTS.md`, `vendor/squidit/php-coding-standards/resources/ai-agent/AGENTS.md`, and the vendor
   [implementation prompt](../../vendor/squidit/php-coding-standards/resources/ai-agent/prompt/02-implementation.md).
   Inspect the neighbouring code before designing a change.
2. Read the [overview](00-overview.md), the [general plan](plan.md), the [open issues](99-open-issues.md), the selected
   chunk plan, and the delivery and review reports of the chunks it depends on.
3. In the first response: name the chunk and the files it will change, check every class it touches against its row in
   the responsibility map, explain every relevant open issue in plain words (what can fail, at least two options, a
   recommendation), and state any ambiguity that affects the public API, a safety guarantee, the test architecture, or
   the agreed scope.
4. Agree on the scope, then wait for the developer's **explicit approval to change files**.

Vendor prompt placeholders:

| Placeholder | Value |
|---|---|
| `{{PLAN_FILES_TO_IMPLEMENT}}` | The general plan, the selected chunk plan, the open issues, and the accepted dependency reports |
| Optional stage restriction | The selected chunk only. Do not implement a later chunk to close a temporary gap without agreement |
| Optional test or coverage expectations | The chunk plan's proof requirements, plus the repository testing rules |
| `{{VERIFICATION_COMMAND}}` | {{AGENT_VERIFICATION}}; {{DEVELOPER_VERIFICATION}}, recorded as pending until the developer supplies the results |
| `{{REPORT_OUTPUT_PATH}}` | `.development/{{FEATURE_FOLDER}}/delivery/<chunk plan filename>` |

After approval: implement only the agreed chunk, discuss any necessary material change before making it (including a
class that would take on a second responsibility), run the available verification, write the delivery report, and set
the chunk's status and link in the overview.

## Start a review session

> Read `.development/{{FEATURE_FOLDER}}/start-chunk.md` and follow its review instructions for chunk 01: its plan,
> delivery report, code changes, and verification. Write the review report. Do not change implementation files unless I
> explicitly ask. Do not create a Git commit.

The reviewer reads the same files as the implementation kickoff and applies the vendor
[review prompt](../../vendor/squidit/php-coding-standards/resources/ai-agent/prompt/03-review.md) to the chunk plan,
delivery report, actual diff, tests, and applicable decisions. Every class the chunk touches is read as a whole and
checked against the responsibility map.

Vendor prompt placeholders:

| Placeholder | Value |
|---|---|
| `{{FILES_OR_PLAN_UNDER_REVIEW}}` | The selected chunk plan, its delivery report, and the diff |
| Optional plan the implementation follows | The general plan, including the responsibility map |
| Optional domain-specific concerns | The chunk's entry under "Review focus per chunk" in the overview |
| `{{REVIEW_OUTPUT_PATH}}` | `.development/{{FEATURE_FOLDER}}/review/<chunk plan filename>` |

Verification during review: {{REVIEW_VERIFICATION}}. Follow the [review guide](review/README.md). If changes are
requested, fix the chunk, update its delivery report, and repeat verification and review.

## Revise a plan

Use the vendor [planning prompt](../../vendor/squidit/php-coding-standards/resources/ai-agent/prompt/01-planning.md)
with the general plan, the chunk plan, the open issues, and the accepted dependency reports. Discuss the change first
and get explicit approval before editing the plan file. Planning approval does not authorise PHP implementation.
