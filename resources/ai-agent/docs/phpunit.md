---
name: phpunit
description: 'PHPUnit rules for test naming, test namespaces, mocks and stubs, exception assertions, and consecutive calls. Read when writing, changing, or reviewing PHPUnit tests.'
---

# PHP Unit Tests

These rules supplement the company-wide `AGENTS.md`.

- Prefer PHPUnit ^13.2. Follow the consuming repository's local Composer constraint when it uses another supported version.
- When testing exceptions, use static calls for exception assertions: `self::expectException`,
  `self::expectExceptionMessageIs`, or `self::expectExceptionMessageIsOrContains` instead of their `$this->` forms.
- If mocking a PSR7 `ResponseInterface` or `RequestInterface` is not possible, use `nyholm/psr7`
- The unit test function name needs to contain the expected outcome suffix: `Succeeds`, `Fails`,
  `ThrowsExceptionOnFailure`, etc.
- Never use the PHPUnit attribute `CoversClass`
- Import the `Throwable` class
- When using data providers in PHPUnit tests, always use attribute syntax
- Unit tests need to be stored in the `tests/Unit` folder and integration tests in the `tests/Integration` folder
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

1. Configure the expected invocation count with `expects(self::exactly(...))`.
2. Configure expected arguments with `withParameterSetsInOrder(...)`, `withParameterSetsInAnyOrder(...)`, or
   `withParameterSetsInPartialOrder(...)`.
3. Configure consecutive return values with `willReturn(...)`.
4. Call `seal()` after configuration when using sealed mock objects.

**Example: Ordered calls with ordered return values**

```php
public function testConsecutiveCallsSucceeds(): void
{
    $myService = self::createMock(MyService::class);
    $myService
        ->expects(self::exactly(2))
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
