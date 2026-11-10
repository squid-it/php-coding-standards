---
name: exceptions-logging
description: 'Rules for creating, translating, and logging exceptions, and for logger calls. Read when the work creates an exception class, throws or catches exceptions, or writes logger calls.'
---

# Exceptions and logging

These rules supplement the company-wide `AGENTS.md`.

## Exceptions

- Exception classes live in `ModuleName/Exception`.
- When translating a caught exception, pass the original as the previous exception.
- Catch only the failures you can handle, and rethrow every other exception unchanged.
- The loop that handles items in a long-running process is the one place that catches every exception: it logs an
  `error` and continues with the next item.

## Logging

- Log an `error` when a developer must act: change the code or configuration so the failure does not happen again.
- Log a `warning` when something happened that can be expected: not ideal, but the code recovered and nobody needs
  to act.
- Errors are what we monitor. A process that logs no errors handled every failure itself.
- Log a failure where it is handled. Code that throws or returns a failure does not also log it.
- Put identifiers in the context array, not in the message: `['workerId' => $workerId]`.
