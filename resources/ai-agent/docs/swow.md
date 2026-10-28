---
name: swow
description: 'Swow coroutine rules for scheduling, SwowTools interfaces, the wait group lifecycle, exception codes, channel listeners, and stopping coroutines. Read when the work uses `Swow\*` or `SquidIT\SwowTools\*` APIs; starts, synchronizes, or stops coroutines; uses channels, wait groups, selectors, or listeners; or handles Swow exceptions.'
---

# Swow coroutines

These rules supplement the company-wide `AGENTS.md`.

## Swow - Dependencies

When a class must create multiple dependency instances during execution, inject a factory. Use `squidit/swow-tools`
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

-----

## Swow - running coroutines

- `Swow\Coroutine::run()` must receive an object implementing `__invoke()`.
- Pass coroutine input through the constructor.

## Swow - Coroutine Schedule Logic

- Swow is cooperative: a coroutine keeps running until it blocks or otherwise yields.
- `Coroutine::run()` schedules a child immediately, but the parent does not continue until the child yields or finishes.
- A CPU-bound child coroutine without I/O behaves like a synchronous call.
- If the main coroutine exits, all child coroutines exit with it. Use a wait group or equivalent synchronization
  primitive to keep the parent alive until work finishes.
- Exceptions thrown inside a child coroutine stay inside that coroutine. Catch and translate them there, then report
  failure through a channel or another synchronization primitive.

-----

## Swow - SwowTools interfaces

Use `squidit/swow-tools` interfaces in public signatures:

- `\SquidIT\SwowTools\Sync\Interface\ChannelInterface` for normal channel dependencies
- `\SquidIT\SwowTools\ChannelSelector\Interface\SelectableChannelInterface` for channels used with
  `SwowChannelListener`, listeners, or selectors
- `\SquidIT\SwowTools\Sync\Interface\WaitGroupInterface` for wait groups
- `\SquidIT\SwowTools\Sync\Interface\ChannelFactoryInterface` and
  `\SquidIT\SwowTools\Sync\Interface\WaitGroupFactoryInterface` when a class must create multiple channels or wait
  groups internally
- `\SquidIT\SwowTools\ChannelSelector\Interface\ChannelListenerInterface` for channel listeners, and
  `\SquidIT\SwowTools\ChannelSelector\Interface\ChannelListenerFactoryInterface` when a class must create several
- `\SquidIT\SwowTools\Coroutine\Interface\CoroutineTerminatorInterface` for stopping coroutines
- Concrete defaults may use `\SquidIT\SwowTools\Sync\SwowChannel`, `\SquidIT\SwowTools\Sync\SwowWaitGroup`,
  `\SquidIT\SwowTools\ChannelSelector\SwowChannelListener`, and their factories

-----

## Swow - WaitGroup Lifecycle Pattern

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
- Do not call `waitGroup->done()` outside the coroutine unless you force-kill it and must compensate because its
  `finally` block will never run

-----

## Swow - Exception handling

Do not hardcode Swow error codes. Use `\SquidIT\SwowTools\Sync\Codes\SwowExceptionUtil` for `SyncException`,
`ChannelException`, and `SelectorException`.

Handle the codes you expect and rethrow every other exception unchanged.

```php
use SquidIT\SwowTools\Sync\Codes\SwowExceptionUtil;
use Swow\ChannelException;
use Swow\SyncException;

try {
    $this->waitGroup->wait(100);
} catch (SyncException $syncException) {
    if (SwowExceptionUtil::isTimeout($syncException) === false) {
        throw $syncException;
    }

    $this->logger->warning('Timeout while waiting on task wait group', ['taskId' => $this->taskId]);
}

try {
    $channelData = $this->resultChannel->pop(100);
} catch (ChannelException $channelException) {
    if (SwowExceptionUtil::isTimeout($channelException) === true) {
        return null;
    }

    if (SwowExceptionUtil::isClosed($channelException) === true) {
        throw ResultChannelClosedException::fromTask($this->taskId, $channelException);
    }

    throw $channelException;
}
```

-----

## Swow - Listening on multiple channels

Prefer `\SquidIT\SwowTools\ChannelSelector\SwowChannelListener` over raw `Swow\Selector` in application code.

- Register one or more callbacks per channel.
- Use `listen()` for a single wait and `listenLoop()` for repeated waits.
- Inspect `ListenResult` for `isTimeout`, `isClosed`, and `isStopRequested`.
- Call `stop()` from another coroutine to break a blocking listen loop.
- Do not close a channel while a selector or listener iteration is active. `SwowChannel::close()` can throw while the
  channel is still registered.

Inject the listener as `ChannelListenerInterface`, or inject `ChannelListenerFactoryInterface` when a class needs
several listeners. Inject the channels as `SelectableChannelInterface`. `HandleWork` and `HandleSignal` are invokable
classes.

```php
$this->channelListener
    ->registerChannelCallback($this->workChannel, new HandleWork())
    ->registerChannelCallback($this->signalChannel, new HandleSignal());

$listenResult = $this->channelListener->listen(500);

if ($listenResult->isTimeout === true) {
    return;
}

if ($listenResult->isClosed === true) {
    throw new RuntimeException(sprintf(
        'Channel closed while worker %s listened for work: %s',
        $this->workerId,
        (string) $listenResult->failureDescription,
    ));
}
```

-----

## Swow - Stopping or killing a coroutine

Stop coroutines with `\SquidIT\SwowTools\Coroutine\Interface\CoroutineTerminatorInterface`. Inject it, or inject
`\SquidIT\SwowTools\Coroutine\Factory\Interface\CoroutineTerminatorFactoryInterface`. The default implementation is
`\SquidIT\SwowTools\Coroutine\SwowCoroutineTerminator`. Write the stages by hand only when the terminator cannot express
the required behavior.

```php
use SquidIT\SwowTools\Coroutine\Data\CoroutineGracefulStopStage;
use SquidIT\SwowTools\Coroutine\Data\CoroutineTerminationRequest;
use SquidIT\SwowTools\Coroutine\Enum\CoroutineTerminationResult;

$coroutineTerminationOutcome = $this->coroutineTerminator->terminate(
    new CoroutineTerminationRequest(
        coroutine: $this->workerCoroutine,
        waitGroup: $this->waitGroup,
        forcedStopThrowable: ForcedWorkerStopException::fromWorker($this->workerId),
        forceStopWaitTimeoutInMs: 500,
        coroutineGracefulStopStage: new CoroutineGracefulStopStage(
            waitTimeoutInMs: 500,
            coroutineGracefulStopAction: new SendWorkerStopMessage($this->controlChannel),
        ),
    ),
);

if ($coroutineTerminationOutcome->coroutineTerminationResult === CoroutineTerminationResult::Killed) {
    $this->logger->warning('Worker killed after forced stop timed out', ['workerId' => $this->workerId]);
}
```

- `ForcedWorkerStopException` implements `ForcedStopThrowableInterface`, so the coroutine body can catch it as a typed
  cleanup signal.
- `SendWorkerStopMessage` implements `CoroutineGracefulStopActionInterface` and pushes the stop message.
- The terminator does not log. Inspect the returned `CoroutineTerminationOutcome` and record what the caller needs.

The terminator stops a coroutine in these stages:

1. Send a stop message through a channel the coroutine listens on.
2. The coroutine must own its `waitGroup->add()` / `waitGroup->done()` lifecycle.
3. Wait outside the coroutine with `waitGroup->wait($timeoutInMs)`.
4. If that times out, inject an exception with `Coroutine->throw()` so the coroutine can unwind inside its own call stack.
5. Wait again.
6. If the coroutine is still alive, call `kill()` and then call `waitGroup->done()` manually.

Coroutine body, which must listen for the stop message:

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

Manual controller side, only when the terminator cannot be used:

```php
$this->controlChannel->push('stop');

try {
    $this->waitGroup->wait(500);

    return;
} catch (SyncException $syncException) {
    if (SwowExceptionUtil::isTimeout($syncException) === false) {
        throw $syncException;
    }
}

$this->workerCoroutine->throw(ForcedWorkerStopException::fromWorker($this->workerId));

try {
    $this->waitGroup->wait(500);

    return;
} catch (SyncException $syncException) {
    if (SwowExceptionUtil::isTimeout($syncException) === false) {
        throw $syncException;
    }
}

if ($this->workerCoroutine->isAlive() === true) {
    $this->workerCoroutine->kill();
    $this->waitGroup->done();
}
```

`kill()` is a last resort. It prevents the coroutine's `finally` blocks from running.
