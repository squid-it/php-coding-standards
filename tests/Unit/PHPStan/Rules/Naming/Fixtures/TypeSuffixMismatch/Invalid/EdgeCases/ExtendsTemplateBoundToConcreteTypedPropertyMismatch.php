<?php

declare(strict_types=1);

namespace TypeSuffixMismatchFixtures\Invalid\EdgeCases;

use TypeSuffixMismatchFixtures\Valid\EdgeCases\TemplateHierarchy\AbstractConnectionContainer;
use TypeSuffixMismatchFixtures\Valid\FooData;

/**
 * @template TConnection of FooData
 * @extends AbstractConnectionContainer<TConnection>
 */
final class ExtendsTemplateBoundToConcreteTypedPropertyMismatch extends AbstractConnectionContainer
{
    /** @var TConnection */
    private object $connection;
}
