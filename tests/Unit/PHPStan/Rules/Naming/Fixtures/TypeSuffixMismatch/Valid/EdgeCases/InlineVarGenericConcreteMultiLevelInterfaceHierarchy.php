<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\EdgeCases\MultiLevelChannelHierarchy\SwowChannel;

function runInlineVarGenericConcreteMultiLevelInterfaceHierarchy(): void
{
    /** @var SwowChannel<bool> $scenarioStartedChannel */
    $scenarioStartedChannel = SwowChannel::withCapacity(2);

    /** @var SwowChannel<int> $requestSuccessCountChannel */
    $requestSuccessCountChannel = SwowChannel::withCapacity(2);

    /** @var SwowChannel<\Throwable> $stopErrorChannel */
    $stopErrorChannel = SwowChannel::withCapacity(2);
}
