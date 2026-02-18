<?php

declare(strict_types=1);

namespace NoServiceInstantiationFixtures\Valid;

final class ReadonlyPropertiesVoInstantiationAllowed
{
    public function run(): ProfileVo
    {
        return new ProfileVo('profile-1');
    }
}
