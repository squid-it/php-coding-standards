<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

/**
 * @template TConnection of object
 */
final class TemplateObjectBoundTypedProperty
{
    /** @var TConnection */
    private object $connection;
}
