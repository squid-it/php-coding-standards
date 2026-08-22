# AGENT.md

When asked to plan, review, diagnose, generate, refactor, or modify anything related to PHP, use these guidelines.

This file provides guidance and instructions for working with PHP code that follows these conventions.

## **Development Workflow**

- Read the repository-specific instructions before planning or performing work.
- Before designing a change, inspect the relevant neighboring code, interfaces, tests, factories, builders, DI
  instantiators, service providers, and configuration.
- Reuse established repository patterns and existing abstractions. Do not introduce a competing pattern for a problem the
  repository already solves consistently.
- Existing repository patterns do not override these company-wide instructions. If they conflict, ask how to proceed.
- First resolve questions that can be answered from the codebase. If uncertainty remains that could materially affect the
  requirements, behavior, public API, architecture, or naming, ask focused clarification questions and wait for answers
  before implementation. Do not guess.
- Once the requirements are clear, proceed without requesting a code review after each file unless explicitly asked.

-----

## **Design and Architecture Standards**

### Descriptive naming

- Class, method, and function names must communicate their responsibility, action, or result without requiring the reader
  to inspect their implementation.
- A method name may rely on its class for context. The class and method names, read together, must make the operation clear.
- Prefer an explicit name over a short or generic name when the domain subject, action, or result would otherwise be
  ambiguous.

### SOLID and simplicity

- Honor SOLID principles where they improve cohesion, dependency direction, testability, or maintainability.
- Keep the design simple and proportionate to the current requirement. Do not over-engineer or introduce abstractions for
  speculative future needs.
- Do not add an interface, factory, builder, instantiator, provider, or other architectural layer merely to demonstrate a
  design principle or bypass an automated rule.

### Object construction roles

- A class ending with `Factory` creates fresh instances during normal application execution. Use a factory when new
  instances must be constructed repeatedly or when construction policy needs to be centralized. Expose a named creation
  method such as `create()`.
- A class ending with `Builder` is a helper for assembling a complex object or object graph from multiple collaborating
  objects. Use a builder only when that construction would otherwise be difficult to understand. Expose a named method
  such as `build()`.
- A class ending with `Instantiator` is exclusively an invokable DI-container definition that resolves or constructs one
  registered service. It may receive container-resolved dependencies through `__invoke()` and must not be used as a
  general application factory.
- The `Provider` suffix is reserved for DI service providers implementing
  `League\Container\ServiceProvider\ServiceProviderInterface`, directly or indirectly. This includes classes extending
  `League\Container\ServiceProvider\AbstractServiceProvider` or a project-specific subclass of it.
- Do not give a class one of these suffixes merely to gain permission to instantiate services.

### Invokable objects

- Declare `__invoke()` only when instances are intentionally passed to an API or boundary that expects a callable, such as
  a DI instantiator, coroutine task, or callback.
- Do not use `__invoke()` as a replacement for a factory's `create()` method, a builder's `build()` method, or a descriptive
  domain method.
- When an API expects a callable, supply an object implementing `__invoke()` instead of an anonymous or arrow function.

-----

## **Enforced Coding Standards**

All created or modified code **must** pass the automated checks defined by the `squidit/php-coding-standards` package. Code that does not pass these rules will not be accepted.

The full rule set, configuration, and examples are documented in:
**`vendor/squidit/php-coding-standards/README.md`**

### What is enforced

Before running verification, inspect the consuming repository's `composer.json` scripts and repository-specific
instructions. Use the commands defined by that repository instead of assuming every repository exposes the same scripts.

- Run `composer auto-review` when that script is available.
- Run `composer fix` when that script is available as the repository's normal full verification workflow.
- When a named verification script is unavailable, use the locally documented equivalent instead of inventing a command.

**PHP-CS-Fixer** (code style formatting):
- Configured via `.php-cs-fixer.dist.php` in the project root
- Run with `composer cs:fix` when that script is available

**PHPStan Stable Rules + Configured Experimental Auto Review Rules:**

The consuming repository's PHPStan configuration determines which experimental rules are enabled. All stable rules and
all locally configured experimental rules must pass. Commonly configured rules include:

- `SingleClassPerFileRule` - one class/interface/trait/enum per file
- `DisallowAnonymousFunctionRule` - no closures or arrow functions; use invokable classes
- `DisallowLogicalNotRule` - no `!` operator; use explicit comparisons (`=== false`, `!== null`)
- `TypeSuffixMismatchRule` - variable/property names must reflect their type (strip `Interface` suffix, keep `Factory`/`Collection`)
- `IterablePluralNamingRule` - iterables of typed objects must use `*List` suffix
- `ForeachValueVariableNamingRule` - foreach value variable must match the singularized iterable or element type
- `LoggerContextKeyCamelCaseRule` - logger context keys must be camelCase
- `EnumBackedValueCamelCaseRule` - string-backed enum values must be camelCase
- `NoServiceInstantiationRule` - no `new ServiceClass()` outside Factory/Builder/Instantiator/enum/test/DI-provider classes
- `ReadonlyClassPromotionRule` - promote to `readonly class` when all properties are readonly

**PHPStan level 9** (static analysis):
- Run with `composer analyse` when that script is available

> **When in doubt about a specific rule's behavior, consult `vendor/squidit/php-coding-standards/README.md` for detailed examples of valid and invalid patterns.**

-----

## **Code Style Standards**

### **Environment**

- **PHP 8.4+ minimum** - use a higher version when the consuming repository confirms it locally through `composer.json`
  or repository-specific instructions.
- Strict typing is mandatory. Use promoted properties, readonly classes, and union types where appropriate. Deviate only
  when the requirement cannot be implemented correctly with those features.
- **No `microtime()`** - **always use `hrtime(true)`** for time measurements.
- **Assume a long-running Swow coroutine environment** - clean up buffers, channels, and long-lived objects explicitly when appropriate.
- **Exceptions must contain enough context for outer-layer logging** - include the main object id, related object ids, the action being performed, and the real failure message.

-----

### **Code Style**

- **Follow PSR-12** as the base, but **prioritize clarity and immutability** over rigid formatting.

- **All native function and method parameters and return types are explicit** - they must not be `mixed` or untyped
  (`void` and `never` are allowed).
- Prefer value objects over unstructured arrays. When an array cannot reasonably be replaced with a value object, document
  the most precise key and value types possible. PHPDoc array value types may use `mixed`; use
  `array<int|string, mixed>` only as the broad fallback.

- **Objects must always be in a valid state** - set all properties via the constructor (promoted properties preferred).

- **Prefer `readonly` classes** over `readonly` properties - if all properties are readonly, declare the class `readonly`.

- **Never use logical NOT (`!`)** - use **explicit comparisons** (`=== true`, `=== false`, `!== null`).

- **Never use arrow functions**, **anonymous functions**, or `array_map`.

- **Use `use` statements** to import classes - **do not import global functions** (for example, `use function bcdiff;` is forbidden).

- **Prefer backed enums** (`string` or `int`) for closed sets of domain values instead of repeating domain string literals.

- **Never use PHP property hooks** (`get` or `set` blocks declared on a property).
- Magic property access methods such as `__get()`, `__set()`, `__isset()`, and `__unset()` are allowed but should be
  avoided when a normal explicit API can express the behavior.

- **Use `try/catch` blocks sparingly** - limit to 5-10 lines. If logic exceeds this, extract it into a private method.

- Prefer constructor injection for object dependencies and depend on interfaces when a useful interface exists.
- A promoted constructor dependency may use a default `new` expression when the class has a sensible default
  implementation.
- State owned exclusively by the object does not need to be injected.
- When a class must create multiple dependency instances during execution, inject a factory. Use `squidit/swow-tools`
  interfaces and factories for Swow primitives:

  ```php
  use SplPriorityQueue;

  public function __construct(
      private readonly ChannelInterface $wakeChannel,
      private readonly ChannelInterface $resultChannel,
      private readonly WaitGroupInterface $mainWaitGroup,
      private readonly WaitGroupInterface $runningTasksWaitGroup,
      private readonly SplPriorityQueue $taskHeap = new SplPriorityQueue(),
      private readonly Lifecycle $lifecycle = new Lifecycle(),
      private readonly TaskSleepCalculatorInterface $sleepCalculator = new DefaultTaskSleepCalculator(),
  ) {}
  ```

- If a channel will be used with `SwowChannelListener` or selectors, type it as `SelectableChannelInterface`.

- **Never rely on `__destruct()` for process lifecycle management** - it is non-deterministic and unsafe in long-running processes.

-----

### Swow - running coroutines

- `Swow\Coroutine::run()` must receive an object implementing `__invoke()`.
- Pass coroutine input through the constructor. Do not use anonymous functions, arrow functions, or `use (...)`.

### Swow - Coroutine Schedule Logic

- Swow is cooperative: a coroutine keeps running until it blocks or otherwise yields.
- `Coroutine::run()` schedules a child immediately, but the parent does not continue until the child yields or finishes.
- A CPU-bound child coroutine without I/O behaves like a synchronous call.
- If the main coroutine exits, all child coroutines exit with it. Use a wait group or equivalent synchronization primitive to keep the parent alive until work finishes.
- Exceptions thrown inside a child coroutine stay inside that coroutine. Catch and translate them there, then report failure through a channel or another synchronization primitive.

-----

### Swow - SwowTools interfaces

Use `squidit/swow-tools` interfaces in public signatures:

- `\SquidIT\SwowTools\Sync\Interface\ChannelInterface` for normal channel dependencies
- `\SquidIT\SwowTools\ChannelSelector\Interface\SelectableChannelInterface` for channels used with listeners or selectors
- `\SquidIT\SwowTools\Sync\Interface\WaitGroupInterface` for wait groups
- `\SquidIT\SwowTools\Sync\Interface\ChannelFactoryInterface` and `\SquidIT\SwowTools\Sync\Interface\WaitGroupFactoryInterface` when a class must create multiple channels or wait groups internally
- Concrete defaults may use `\SquidIT\SwowTools\Sync\SwowChannel`, `\SquidIT\SwowTools\Sync\SwowWaitGroup`, `\SquidIT\SwowTools\ChannelSelector\SwowChannelListener`, and their factories

-----

### Swow - WaitGroup Lifecycle Pattern

All coroutine classes must manage their own wait group lifecycle inside `__invoke()`.

```php
public function __invoke(): void
{
    $this->waitGroup->add();

    try {
        $this->executeBusinessLogic();
    } finally {
        $this->waitGroup->done();
    }
}
```

Rules:

- Never call `waitGroup->add()` externally before `Coroutine::run()`
- Keep business logic in private methods when the `__invoke()` body grows
- Always use `try/finally` around the coroutine body
- Do not call `waitGroup->done()` outside the coroutine unless you force-kill it and must compensate because its `finally` block will never run

-----

### Swow - Exception handling

Do not hardcode Swow error codes. Use `\SquidIT\SwowTools\Sync\Codes\SwowExceptionUtil` for `SyncException`, `ChannelException`, and `SelectorException`.

```php
use SquidIT\SwowTools\Sync\Codes\SwowExceptionUtil;
use Swow\ChannelException;
use Swow\SyncException;

try {
    $waitGroup->wait(100);
} catch (SyncException $syncException) {
    if (SwowExceptionUtil::isTimeout($syncException) === true) {
        echo 'Timeout while waiting on wait group';
    }
}

try {
    $channelData = $channel->pop(100);
} catch (ChannelException $channelException) {
    if (SwowExceptionUtil::isTimeout($channelException) === true) {
        echo 'Timeout while popping value from channel';
    } elseif (SwowExceptionUtil::isClosed($channelException) === true) {
        echo 'Channel was closed while popping value from channel';
    }
}
```

-----

### Swow - Listening on multiple channels

Prefer `\SquidIT\SwowTools\ChannelSelector\SwowChannelListener` over raw `Swow\Selector` in application code.

- Type channels as `SelectableChannelInterface` when they participate in listener or selector flows.
- Register one or more callbacks per channel.
- Use `listen()` for a single wait and `listenLoop()` for repeated waits.
- Inspect `ListenResult` for `isTimeout`, `isClosed`, and `isStopRequested`.
- Call `stop()` from another coroutine to break a blocking listen loop.
- Do not close a channel while a selector or listener iteration is active. `SwowChannel::close()` can throw while the channel is still registered.

```php
use SquidIT\SwowTools\ChannelSelector\SwowChannelListener;
use SquidIT\SwowTools\Sync\SwowChannel;
use Swow\Channel;

$channelListener = new SwowChannelListener();
$workChannel = new SwowChannel(new Channel(1));
$signalChannel = new SwowChannel(new Channel(1));

$channelListener
    ->registerChannelCallback($workChannel, new HandleWork())
    ->registerChannelCallback($signalChannel, new HandleSignal());

$listenResult = $channelListener->listen(500);

if ($listenResult->isTimeout === true) {
    return;
}

if ($listenResult->isClosed === true) {
    throw new RuntimeException((string) $listenResult->failureDescription);
}
```

-----

### Swow - Stopping or killing a coroutine

Stop coroutines in stages:

1. Send a stop message through a channel the coroutine listens on.
2. The coroutine must own its `waitGroup->add()` / `waitGroup->done()` lifecycle.
3. Wait outside the coroutine with `waitGroup->wait($timeoutInMs)`.
4. If that times out, inject an exception with `Coroutine->throw()` so the coroutine can unwind inside its own call stack.
5. Wait again.
6. If the coroutine is still alive, call `kill()` and then call `waitGroup->done()` manually.

Coroutine body:

```php
public function __invoke(): void
{
    $this->waitGroup->add();

    try {
        while (true) {
            $message = $this->controlChannel->pop();

            if ($message === 'stop') {
                return;
            }

            $this->handleMessage($message);
        }
    } finally {
        $this->waitGroup->done();
    }
}
```

Controller side:

```php
$controlChannel->push('stop');

try {
    $waitGroup->wait(500);

    return;
} catch (SyncException $syncException) {
    if (SwowExceptionUtil::isTimeout($syncException) === false) {
        throw $syncException;
    }
}

$workerCoroutine->throw(new RuntimeException('Forced coroutine shutdown requested'));

try {
    $waitGroup->wait(500);

    return;
} catch (SyncException $syncException) {
    if (SwowExceptionUtil::isTimeout($syncException) === false) {
        throw $syncException;
    }
}

if ($workerCoroutine->isAlive() === true) {
    $workerCoroutine->kill();
    $waitGroup->done();
}
```

`kill()` is a last resort. It prevents the coroutine's `finally` blocks from running.

-----

### Variable and value naming

- When instantiating classes, use the class name as the variable name in camelCase format
- When assigning method return values to a variable, use the return type as the variable name in camelCase format
- If the return type ends with `Interface`, the variable name needs to omit `Interface`
- When assigning an array of objects to a variable, use the object name post-fixed with `List` as the variable name
- Logging context keys or array keys are always in camelCase
- Enum values are always in camelCase

-----

### PHP Unit Tests

- Prefer PHPUnit 13.*. Follow the consuming repository's local Composer constraint when it uses another supported version.
- When testing exceptions, use static calls for exception assertions (`self::expectException` and `self::expectExceptionMessage`)
- If mocking a PSR7 `ResponseInterface` or `RequestInterface` is not possible, use `nyholm/psr7`
- The unit test function name needs to contain the expected outcome suffix: `Succeeds`, `Fails`, `ThrowsExceptionOnFailure`, etc.
- Never use the PHPUnit attribute `CoversClass`
- Import the `Throwable` class
- Unit tests need to be stored in the `tests\Unit` folder and integration tests in the `tests\Integration` folder
- If a class name has a namespace such as `Vendor\Package\Module`, insert the `Tests` prefix after the first namespace word and `Unit` or `Integration` after the second word. Example unit test: `Vendor\Package\Module` => `Vendor\Tests\Package\Unit\Module`
- When writing PHPUnit tests, use `setUp()` where appropriate
- Use mocks when the test verifies interactions such as calls, arguments, or invocation counts
- Use stubs when the test only needs a dependency to supply values or behavior
- Reuse mocks and stubs through `setUp()` when doing so meaningfully reduces boilerplate without obscuring individual tests
- Use explicit `MockObject`/`Stub` intersection property types to prevent PHPStan warnings
- Aim for 100% test coverage; 95% is acceptable for complicated cases
- Newer PHPUnit versions report a notice when a mock has no expectation. Use a stub instead of suppressing that notice
  when no interaction is being verified

`$this->expectException` => `self::expectException`
`$this->expectExceptionMessage` => `self::expectExceptionMessage`

#### PHPUnit: Mocking Consecutive Calls

- **Rule:** Do not use `withConsecutive` (removed in PHPUnit 10).
- **Preferred PHPUnit 13+ replacement:** Use parameter-set matchers:
  - `withParameterSetsInOrder(...)` when call order matters.
  - `withParameterSetsInAnyOrder(...)` when all calls must happen but order does not matter.
  - `withParameterSetsInPartialOrder(...)` when only some calls must be pinned to specific positions.
- **Return values:** Use variadic `willReturn(...)` for consecutive return values.
- **Restriction:** Do not use anonymous functions or closures.
- **Fallback:** Use `willReturnCallback($this->privateCallbackMethod(...))` only when the assertion or return logic cannot be expressed with parameter-set matchers, constraints, `willReturn(...)`, or `willReturnStrictMap(...)`.

**Implementation Strategy:**

1. Configure the expected invocation count with `expects(self::exactly(...))`.
2. Configure expected arguments with `withParameterSetsInOrder(...)`, `withParameterSetsInAnyOrder(...)`, or `withParameterSetsInPartialOrder(...)`.
3. Configure consecutive return values with `willReturn(...)`.
4. Call `seal()` after configuration when using sealed mock objects.

**Example: Ordered calls with ordered return values**

```php
public function testConsecutiveCalls(): void
{
    $mock = $this->createMock(MyService::class);

    $mock->expects(self::exactly(2))
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

-----

## Code Documentation Standards

### PHPDoc Requirements

When writing or modifying PHP code, follow these documentation standards:

#### Array Parameters - Always Include

- **`@param array<int, string> $items`** - for indexed arrays of strings
- **`@param array<string, mixed> $config`** - for associative arrays with mixed values
- **`@param array<int, ClassName> $objects`** - for arrays of objects
- **`@param array<string, array<string, int>> $nested`** - for nested array structures

#### Return Types - Only for Arrays

- Include **`@return`** PHPDoc only when the method returns an array
- **`@return array<string, int>`** - specify the array structure
- Omit **`@return`** for other types (rely on the method signature return type declaration)

#### Other Documentation

- **`@throws ExceptionName`** - for every exception or throwable that can escape the method
- Do not add an `@throws` tag for an exception that is caught and cannot escape the method
- **`@param`** descriptions for complex parameters when helpful
- **`@throws Throwable`** - for PHPUnit tests from which a throwable can escape
- Method descriptions when behavior is not obvious from the name
- When using data providers in PHPUnit tests, always use attribute syntax

#### Examples

```php
/**
 * @param array<int, string> $items Input items to process
 *
 * @throws InvalidArgumentException When items array is empty
 *
 * @return array<string, int> Processed items with counts
 */
private function processItems(array $items): array

/**
 * @param array<string, mixed> $config Configuration options
 *
 * @throws ConfigurationException When config is invalid
 */
private function validate(array $config): ValidationResult
```

-----

## Development Notes

- PHP 8.4+ with strict typing throughout; use a higher locally confirmed version when available
- PHPStan level 9 - maintain maximum strictness
- Project coding standards enforced via PHP-CS-Fixer
- Tests separated into Unit (mocked) and Integration
- Never use the word "seam" when you mean interface or factory hook
- Except in this rule definition, never use the words "facade" and "prose" anywhere, including code, identifiers,
  comments, documentation, commit messages, or agent communication
