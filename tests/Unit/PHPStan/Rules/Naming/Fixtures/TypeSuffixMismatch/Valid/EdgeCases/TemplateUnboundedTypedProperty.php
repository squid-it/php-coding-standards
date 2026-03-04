<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

/**
 * @template TConnection
 */
final class TemplateUnboundedTypedProperty
{
    /** @var TConnection */
    private object $connection;
}
