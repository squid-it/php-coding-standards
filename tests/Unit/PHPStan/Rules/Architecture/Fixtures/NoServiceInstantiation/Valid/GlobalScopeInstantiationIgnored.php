<?php

declare(strict_types=1);

namespace NoServiceInstantiationFixtures\Valid;

final class GlobalScopeService
{
}

function runGlobalScopeInstantiation(): GlobalScopeService
{
    return new GlobalScopeService();
}
