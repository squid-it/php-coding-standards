---
name: phpunit
description: 'PHPUnit rules for test naming, test namespaces, mocks and stubs, exception assertions, consecutive calls, and path handling tests. Read when writing, changing, or reviewing PHPUnit tests.'
---

# PHP Unit Tests

These rules supplement the company-wide `AGENTS.md`.

- Prefer PHPUnit ^13.2. Follow the consuming repository's local Composer constraint when it uses another supported version.
- When testing exceptions, use `$this->` form calls for exception assertions: `$this->expectException`,
  `$this->expectExceptionMessageIs`, or `$this->expectExceptionMessageIsOrContains` instead of their `self::` forms.
- Call `$this->createMock(...)` and `$this->createStub(...)`, and use `$this->` for invocation-count matchers such as
  `$this->once()` and `$this->exactly(...)`. Assertions and constraint matchers use `self::`: `self::assertSame(...)`,
  `self::stringStartsWith(...)`. PHP-CS-Fixer enforces all of these except `createMock` and the exception assertions.
- If mocking a PSR7 `ResponseInterface` or `RequestInterface` is not possible, use `nyholm/psr7`
- The unit test function name needs to contain the expected outcome suffix: `Succeeds`, `Fails`,
  `ThrowsExceptionOnFailure`, etc.
- Never use the PHPUnit attribute `CoversClass`
- Import the `Throwable` class
- When using data providers in PHPUnit tests, always use attribute syntax
- Unit tests need to be stored in the `tests/Unit` folder and integration tests in the `tests/Integration` folder
- A unit test exercises one class and replaces its service dependencies with mocks or stubs. It must not depend on a
  database, network, running service, or forked process. A test that needs any of those, or that verifies several real
  services working together, is an integration test.
- If a class name has a namespace such as `Vendor\Package\Module`, insert the `Tests` prefix after the first namespace
  word and `Unit` or `Integration` after the second word. Example unit test:
  `Vendor\Package\Module` => `Vendor\Tests\Package\Unit\Module`
- When writing PHPUnit tests, use `setUp()` where appropriate
- Use mocks when the test verifies interactions such as calls, arguments, or invocation counts
- Use stubs when the test only needs a dependency to supply values or behavior
- Reuse mocks and stubs through `setUp()` when doing so meaningfully reduces boilerplate without obscuring individual tests
- Use explicit `MockObject`/`Stub` intersection property types to prevent PHPStan warnings
- Aim for 100% test coverage; 95% is acceptable for complicated cases
- New code must not rely on `AllowMockObjectsWithoutExpectations`.
- Existing uses of `AllowMockObjectsWithoutExpectations` may remain.
  Do not proactively remove the attribute or refactor existing tests to eliminate it unless explicitly requested.
- Adding tests to an existing class that carries the attribute does not require removing it. The new tests must use
  appropriate mocks or stubs without relying on the attribute to suppress missing-expectation notices.

## Path handling tests

Code runs on Windows and Linux (see the company-wide `AGENTS.md`), so path handling is tested in both formats on every
host:

- Unit tests for code that parses, normalizes, compares, or maps paths use a data provider with Unix-style and
  Windows-style inputs: `/` and `\` separators, absolute and relative paths, trailing separators, the Unix root, a drive
  root (`C:\`) and a drive-relative path (`C:`), UNC paths (`\\server\share`), and sibling directories that share a
  name prefix (`Settings` and `SettingsExtra`). Include the cases that apply to the code under test.
- Keep these unit tests separate from real filesystem operations: a Windows path in a unit test on Linux does not need
  to exist. Filesystem tests use the host's temporary directory and build paths with the host's separator.
- Do not hard-code `/` in expected native paths, and do not assume Unix permission behavior on Windows.
- A test for code that only one platform supports declares that platform with `#[RequiresOperatingSystemFamily]`.

## PHPUnit: Mocking Consecutive Calls

- **Rule:** Do not use `withConsecutive` (removed in PHPUnit 10).
- **Preferred PHPUnit 13+ replacement:** Use parameter-set matchers:
  - `withParameterSetsInOrder(...)` when call order matters.
  - `withParameterSetsInAnyOrder(...)` when all calls must happen but order does not matter.
  - `withParameterSetsInPartialOrder(...)` when only some calls must be pinned to specific positions.
- **Return values:** Use variadic `willReturn(...)` for consecutive return values.
- **Restriction:** Do not use anonymous functions or closures.
- **Fallback:** Use `willReturnCallback($this->privateCallbackMethod(...))` only when the assertion or return logic
  cannot be expressed with parameter-set matchers, constraints, `willReturn(...)`, or `willReturnStrictMap(...)`.

**Implementation Strategy:**

1. Configure the expected invocation count with `expects($this->exactly(...))`.
2. Configure expected arguments with `withParameterSetsInOrder(...)`, `withParameterSetsInAnyOrder(...)`, or
   `withParameterSetsInPartialOrder(...)`.
3. Configure consecutive return values with `willReturn(...)`.
4. Call `seal()` after configuration when using sealed mock objects, do not call `seal()` in `setUp()`.

**Example: Ordered calls with ordered return values**

```php
public function testConsecutiveCallsSucceeds(): void
{
    $myService = $this->createMock(MyService::class);
    $myService
        ->expects($this->exactly(2))
        ->method('execute')
        ->withParameterSetsInOrder(
            [self::stringStartsWith('first')], // with argument constraint
            ['second'],
        )
        ->willReturn(
            100,
            200,
        )
        ->seal();

    // ... run test
}
```
