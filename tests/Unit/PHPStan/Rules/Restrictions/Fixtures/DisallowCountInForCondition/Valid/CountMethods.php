<?php

declare(strict_types=1);

namespace DisallowCountInForConditionFixtures\Valid\CountMethods;

final class Counter
{
    public static function count(): int
    {
        return 3;
    }

    public function checkMethods(): void
    {
        for ($index = 0; $index < $this->count(); ++$index) {
            echo $index;
        }

        for ($index = 0; $index < self::count(); ++$index) {
            echo $index;
        }
    }
}
