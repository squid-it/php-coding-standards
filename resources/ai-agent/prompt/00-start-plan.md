# Prompt — Start a new development plan

> Replace every `{{PLACEHOLDER}}` before sending. Delete any optional section you do not need.

Use this prompt once, when a new module or feature starts. The session sets up a development folder from the
templates in this package and writes the plan outline. Every later session (writing a chunk plan, implementing,
reviewing) starts from the `start-chunk.md` that this session creates.

## Read first

```
AGENTS.md
vendor/squidit/php-coding-standards/resources/ai-agent/docs/architecture.md
vendor/squidit/php-coding-standards/resources/ai-agent/prompt/01-planning.md
vendor/squidit/php-coding-standards/resources/ai-agent/template/development/   (every file, including subfolders)
{{ADDITIONAL_CONTEXT_FILES}}
```

`AGENTS.md` chains to the company-wide agent instructions. Read "SOLID and simplicity" and "Single responsibility" in
`architecture.md` with care: the responsibility map in the outline is built from them. The plan requirements, module
structure, and naming rules in `01-planning.md` apply to the outline as well. Inspect the neighbouring code, interfaces,
tests, and service providers before proposing a design.

## What we want to develop

{{DESCRIBE_THE_MODULE_OR_FEATURE}}

{{OPTIONAL: current behaviour, constraints, known risks, the platform it must run on}}

## The development folder

Every module or feature gets its own folder under `.development/`. Suggest a name; the developer decides. The default
suggestion is `<feature>-implementation`, for example `cli-process-implementation`.

```text
.development/{{FEATURE_FOLDER}}/
├── README.md             for a developer: what this folder is, reading order, how to start each session
├── 00-overview.md        current state, chunk table with status and links, "How we work"
├── plan.md               the general plan: the finished module (problem, design, API, tests, acceptance criteria)
├── 01-<slug>.md …        one plan per chunk: what that chunk builds and proves
├── 99-open-issues.md     open choices and known limits, with options and decisions
├── start-chunk.md        the messages that start a planning, implementation, or review session
├── delivery/             one report per implemented chunk (historical record)
└── review/               one report per review (historical record)
```

| File | Role | Updated when |
|---|---|---|
| `README.md` | Entry point for a developer new to the folder | The folder structure or the session flow changes |
| `00-overview.md` | The only place a chunk's status is kept | A chunk changes status |
| `plan.md` | The single source for the design | A decision is made; it is written into the section it changes |
| Chunk plans | What one chunk builds and proves | Before implementation; afterwards only through an agreed change |
| `99-open-issues.md` | Unresolved choices and accepted limits | An issue is raised or decided |
| `start-chunk.md` | Session starters and the placeholder values for the `01`, `02`, and `03` prompts | Verification commands or report paths change |
| `delivery/`, `review/` | Evidence of what happened | Written once; a follow-up is added as a new section, earlier text is not rewritten |

Chunk numbering:

- Chunks are numbered `01`, `02`, … in implementation order. `00` and `99` are reserved.
- A chunk inserted later between two others takes a letter suffix: `05-b` follows `05`. The overview explains where
  each side chunk fits and why.
- A dropped chunk keeps its number and file, with status Dropped, so links and history stay valid.

## Rules for the documents

- Every plan, overview, register, and report starts with one current-state line. It says where the document stands
  now, not how it got there. History belongs in the delivery and review reports.
- A chunk's status lives only in the overview table. `README.md` and `start-chunk.md` have no current-state line;
  they link to the overview.
- Decisions are written into the section they change, not added as amendments at the top.
- Refer to sections, issues, and decisions by name, never by number. Write methods as `ClassName::method()`.

## Single responsibility comes first

The responsibility map is the most important part of the outline. A module is built over many chunks, and each chunk
session sees mainly its own diff. Every rule that makes a new class costly (agreed names, module folders, suffix
rules, DI wiring) makes the class that already holds the state the cheapest place for new code. Without a map agreed
up front, that class collects every new behaviour and a later chunk has to split it.

- Every class in the outline gets one row in the map: its responsibility in one sentence without "and", its reason
  to change, and what it must not do.
- Separate deciding from doing. The class that decides what happens does not also carry out the mechanics; the
  mechanics sit behind interfaces, so the decisions can be unit tested with stubs.
- Name exclusive ownership explicitly: "`X` is the only class that names `Y`", "only `X` logs".
- Validation sits in the object that carries the data.
- A class that does not fit in one row is two classes. Propose the split in the outline, not during implementation.

## What this session produces

The focus is the plan outline. Chunk plans may follow in a second session.

1. **`plan.md` as an outline**, from the template:
   - Problem, Goal, and Solution in one paragraph;
   - Scope (in and out), and Preconditions;
   - Public API: the directory layout following the module structure in `01-planning.md`, the responsibility map,
     and the interfaces written out completely;
   - Tests: which suites prove what;
   - Acceptance criteria.

   A section that needs more design lists the questions it must answer instead of guesses. Decisions and Risks may
   stay empty.
2. **`00-overview.md`** with the proposed chunks, each one Planned, and "How we work" filled in.
3. **`99-open-issues.md`** with every question from the outline that this session does not decide.
4. **`README.md`, `start-chunk.md`, `delivery/README.md`, and `review/README.md`** from the templates, with every
   placeholder filled in.
5. **Chunk plans, only when agreed in this session**, from `chunk-plan.md`. Otherwise the next session uses
   "Write a chunk plan" in `start-chunk.md`.

Rules for splitting into chunks:

- Each chunk delivers a working, testable slice and leaves the test suite green.
- The order follows the dependencies: foundation (interfaces, value objects, enums) first, documentation and final
  verification last.
- A chunk is small enough to review in one session. When a chunk needs both a refactor and new behaviour, the
  refactor is its own chunk.

## Filling the templates

- The templates use `{{PLACEHOLDER}}` markers, including the guidance text inside each section. Replace every one.
- Copy the generic rules as they are: approval, verification records, reports, commits, writing docs. Change them only
  when the developer asks.
- Take the project-specific values from the repository: the `composer.json` scripts, the verification section of
  `AGENTS.md`, and the platforms the module must run on. When a value is unclear, ask; do not guess which commands
  run where.
- Links to the prompts are relative to the new folder:
  `../../vendor/squidit/php-coding-standards/resources/ai-agent/prompt/`.
- When the files are written, search the folder for `{{` and report any marker that is left. The placeholder names in
  the "Vendor prompt placeholders" tables of `start-chunk.md` (the left column) name the vendor prompts' own
  placeholders and stay as they are.

## Rules of engagement

- Ask clarification questions before writing anything.
- Present in chat first: the folder name, the outline's Problem, Goal, and Solution, the responsibility map, the
  proposed chunks, and the open questions. Class and folder names are agreed before they are written into a file.
- Change no file until the developer explicitly approves. That approval covers creating this folder and its files
  only. Write no PHP in this session.
- Do not create a Git commit.

## Report

End the session with:

- every file created;
- every open issue registered;
- the message that starts the next session, copied from `start-chunk.md` with the chunk number filled in.
