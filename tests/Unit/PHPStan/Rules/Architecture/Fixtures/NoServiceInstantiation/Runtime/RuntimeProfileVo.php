<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Rules\Architecture\Fixtures\NoServiceInstantiation\Runtime;

final class RuntimeProfileVo
{
    public function __construct(
        public readonly string $profileId,
    ) {}

    public function getProfileId(): string
    {
        return $this->profileId;
    }
}
