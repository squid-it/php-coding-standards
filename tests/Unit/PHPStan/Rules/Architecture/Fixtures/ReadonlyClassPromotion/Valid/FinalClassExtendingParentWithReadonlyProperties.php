<?php

declare(strict_types=1);

namespace ReadonlyClassPromotionFixtures\Valid;

class ExtendingReadonlyParent {}

final class FinalClassExtendingParentWithReadonlyProperties extends ExtendingReadonlyParent
{
    public readonly int $id;
}


