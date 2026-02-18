<?php

declare(strict_types=1);

namespace NoServiceInstantiationFixtures\Valid;

final class ProfileVo
{
    public function __construct(
        public readonly string $profileId,
    ) {}

    public function getProfileId(): string
    {
        return $this->profileId;
    }
}
