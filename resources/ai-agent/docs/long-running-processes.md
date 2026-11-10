---
name: long-running-processes
description: 'Rules for long-running processes that recover from failures without a developer: retries, timeouts, failure isolation, startup, and stopping. Read when the work writes or changes a worker, consumer, scheduler, supervisor, or any loop that runs until it is stopped.'
---

# Long-running processes

These rules supplement the company-wide `AGENTS.md`. Log levels follow
`vendor/squidit/php-coding-standards/resources/ai-agent/docs/exceptions-logging.md`.

A long-running process must recover from expected failures on its own. A developer only acts on logged errors.

Never decide on your own to stop work or the process after a failure. What happens when a failure does not go away,
such as retrying less often or storing the work for later, is a decision for the developer. Ask when the plan does not
say.

## Recover from expected failures

- Treat a lost connection, a timeout, or a dependency that is briefly unavailable as expected. Reconnect or retry,
  and log a `warning`.
- Retry with an increasing delay up to a maximum delay. Never retry in a loop without a delay.
- Give every blocking wait a timeout, so a stuck dependency cannot block the process forever.
- When retries keep failing past a limit, such as 10 attempts or 5 minutes, log an `error`. Logging the error does not
  stop the work.

## Isolate failures

- A failure while handling one item (a message, task, or request) must not stop the process. The loop that handles
  the items logs the failure and continues with the next item.
- An exception the code does not expect is a bug: log it as an `error`.

## Startup and stopping

- When the process reaches a state it cannot recover from, log an `error`.
- Rebuild state at startup instead of relying on state from the previous run.
- Stop gracefully when asked: finish or hand back the current item, then exit. See "Stopping or killing a coroutine"
  in `vendor/squidit/php-coding-standards/resources/ai-agent/docs/swow.md`.

## Resources

- Follow "Coroutine runtime" in the company-wide `AGENTS.md` for cleaning up buffers, channels, and long-lived
  objects.
- Give long-lived arrays and caches a maximum size, so memory use stays bounded however long the process runs.
