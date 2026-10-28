---
name: date-time
description: 'Date, time, and timezone rules, with UTC as the default. Read when the work creates, parses, compares, or serializes dates, times, or timezones.'
---

# Date and time

These rules supplement the company-wide `AGENTS.md`.

- UTC is the default timezone for application date and time values.
- Interpret date and time values without an explicit timezone or offset as UTC.
  A missing offset or `Z` is not, by itself, an ambiguity or defect.
- When input explicitly carries a non-UTC timezone or offset, handling that input is an implementation decision that
  must follow developer-approved requirements. Do not silently reinterpret it as UTC or automatically introduce
  conversion.
- If the required handling of non-UTC input is unspecified, ask the developer before implementing timezone conversion
  or normalization.
- Date-time serialization must follow the agreed output format and timezone requirements. Do not add offsets, change
  formats, or introduce normalization as an unsolicited correction.
- `SquidIT\SwowTools\Time\Formatter\UtcDateTimeFormatter` normalizes values to UTC. Use it when UTC-normalized
  serialization is required by the agreed implementation. Its use must not implicitly decide how non-UTC input should
  be handled.
