<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\VoDtoClassifier;

final readonly class FromToClaimsDto
{
    public function __construct(
        public string $subject,
    ) {}

    /**
     * @param array<string, string> $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            subject: $payload['subject'] ?? '',
        );
    }

    /**
     * @return array<string, string>
     */
    public function toPayload(): array
    {
        return [
            'subject' => $this->subject,
        ];
    }
}
