# {{MODULE_NAME}} development

This folder holds the plans and records for building `{{MODULE_PATH}}`: {{MODULE_DESCRIPTION}}. The work is built in
reviewable steps called **chunks**. Where the work stands is in the [overview](00-overview.md).

## Folder structure

```text
.development/{{FEATURE_FOLDER}}/
├── README.md             you are here: structure and how to start
├── 00-overview.md        current state, chunk order and status, how we work
├── plan.md               the general plan: the finished module (problem, design, API, tests, acceptance criteria)
├── 01-<slug>.md …        one plan per chunk: what that chunk builds and proves
├── 99-open-issues.md     open choices and known limits, with options and decisions
├── start-chunk.md        the messages that start a planning, implementation, or review session
├── delivery/             one report per implemented chunk (historical record)
└── review/               one report per review (historical record)
```

- **`plan.md` is the single source for the design.** When a chunk makes a decision, it is written into the general
  plan, in the section it belongs to.
- **The responsibility map in `plan.md`** gives every class one responsibility. Implementation and review are checked
  against it.
- **Chunk plans** say what one chunk builds and proves. A side chunk has a letter suffix (`05-b`); the overview
  explains where it fits.
- **Delivery and review reports** record what happened and are not rewritten afterwards. Their guides are
  [`delivery/README.md`](delivery/README.md) and [`review/README.md`](review/README.md).

## New here? Read in this order

1. [`00-overview.md`](00-overview.md): where the work stands and the rules we work by ("How we work").
2. [`plan.md`](plan.md): start with "Problem", "Goal", and "Solution in one paragraph", then the responsibility map.
3. [`99-open-issues.md`](99-open-issues.md): what is still undecided or accepted as a limit.
4. The plan of the chunk you will work on.

The repository-wide rules come first: `AGENTS.md` and the company-wide standards it links to in
`vendor/squidit/php-coding-standards/`.

## Write a chunk plan

1. Find the next chunk without a plan file in the [overview](00-overview.md) progress table.
2. Open a new session and paste the chunk-plan starter from [`start-chunk.md`](start-chunk.md), with the chunk number
   filled in.
3. The session discusses scope, responsibilities, and open issues first, and writes the plan only after you explicitly
   approve.

## Start development

1. Find the next chunk that is Ready for implementation in the [overview](00-overview.md).
2. Open a new session and paste the implementation starter from [`start-chunk.md`](start-chunk.md), with the chunk
   number filled in.
3. The session discusses the chunk's open issues and scope first, and changes no files until you explicitly approve.
4. After implementation the session runs {{AGENT_VERIFICATION}}. You run {{DEVELOPER_VERIFICATION}}. The session
   writes `delivery/<chunk plan filename>` and updates the overview.

## Start a review

1. Open a new session and paste the review starter from [`start-chunk.md`](start-chunk.md), with the chunk number
   filled in.
2. The reviewer checks the chunk plan, its delivery report, the diff, the responsibility map, and the verification
   results ({{REVIEW_VERIFICATION}}), and writes `review/<chunk plan filename>`.
3. After you approve the review, commit the chunk yourself. Agents never create a Git commit.

## Change a plan

To revise a chunk plan or the general plan, follow "Revise a plan" in [`start-chunk.md`](start-chunk.md): discuss the
change first and approve it before the file is edited.
