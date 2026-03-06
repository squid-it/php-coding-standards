<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Valid\EdgeCases;

final readonly class AggregateTypedAssignmentAllowsListAlias
{
    public function __construct(private DefinitionAggregate $definitionAggregate) {}

    public function run(): void
    {
        $definitionList = $this->definitionAggregate;
    }
}
