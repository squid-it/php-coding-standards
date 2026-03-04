<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\ChannelInterface;

final class InterfaceWordInVariableNamesAllowed
{
    private ChannelInterface $readInterfaceChannel;

    public function __construct(
        private ChannelInterface $writeInterfaceChannel,
    ) {}

    public function run(ChannelInterface $channel): void
    {
        $localInterfaceChannel = $channel;
        $this->readInterfaceChannel = $localInterfaceChannel;
    }
}
