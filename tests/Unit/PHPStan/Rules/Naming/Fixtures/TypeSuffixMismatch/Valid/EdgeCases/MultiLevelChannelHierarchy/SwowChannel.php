<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases\MultiLevelChannelHierarchy;

/**
 * @template TValue
 * @implements SwowSelectableChannelInterface<TValue>
 */
final class SwowChannel implements SwowSelectableChannelInterface
{
    /** @return self<mixed> */
    public static function withCapacity(int $capacity): self
    {
        if ($capacity < 1) {
            return new self();
        }

        return new self();
    }
}
