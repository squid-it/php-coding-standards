---
name: swow-process
description: 'Rules for the process APIs of the custom Swow build. Read when the work forks processes, waits on child processes, reads child exit status, or handles signals.'
---

# Swow - Swow Process APIs

These rules supplement the company-wide `AGENTS.md`.

Forking processes requires our custom Swow build, `squid-it/swow-custom`. It is installed through Composer as
`"swow/swow": "dev-http2"`, so `vendor/swow/swow/` contains the custom build, not upstream Swow.

Before changing process or signal handling, read `vendor/swow/swow/documentation/fork-process.md` and the relevant
documentation under `vendor/swow/swow/documentation/`. Verify available APIs against the installed vendor stubs and
implementation; do not assume upstream Swow behavior applies to this custom build.

Use `Swow\Process\ForkProcess`, `Swow\Process\ProcessExitStatus`, and `Swow\Signal` for forking, waiting, child exit
status, and signalling. Follow the custom build's callback, waiting, and cleanup rules. Do not introduce `pcntl_*`
calls in project-owned code, including tests, fixtures, and scripts.
