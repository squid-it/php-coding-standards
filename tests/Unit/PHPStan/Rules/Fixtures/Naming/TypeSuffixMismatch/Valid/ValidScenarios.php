<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid;

interface ChannelInterface {}

class FooData {}

class BarData {}

final class ValidScenarios
{
    private FooData $fooData;
    private ChannelInterface $readChannel;

    public function __construct(
        private FooData $initialFooData,
        private ChannelInterface $writeChannel,
    ) {}

    public function run(FooData $fooData, FooData|BarData $fooDataOrBarData): void
    {
        $localFooData    = new FooData();
        $clonedFooData   = clone $fooData;
        $selectedFooData = $fooDataOrBarData;

        $this->fooData = $localFooData;

        if ($selectedFooData instanceof FooData) {
            $this->fooData = $clonedFooData;
        }
    }
}
