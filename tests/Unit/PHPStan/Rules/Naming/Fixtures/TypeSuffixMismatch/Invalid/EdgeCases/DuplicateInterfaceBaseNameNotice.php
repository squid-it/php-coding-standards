<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\EdgeCases\DuplicateBaseName\A\ChannelInterface as AChannelInterface;
use TypeSuffixMismatchFixtures\Valid\EdgeCases\DuplicateBaseName\B\ChannelInterface as BChannelInterface;

final class DuplicateInterfaceBaseNameNotice
{
    private AChannelInterface|BChannelInterface $channel;
}
