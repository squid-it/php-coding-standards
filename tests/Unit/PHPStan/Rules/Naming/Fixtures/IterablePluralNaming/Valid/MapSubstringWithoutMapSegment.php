<?php

declare(strict_types=1);

namespace IterablePluralNamingFixtures\Valid;

final class MapSubstringWithoutMapSegment
{
    public function run(Node $node): void
    {
        $mapperNodes          = [$node];
        $heatmapNodes         = [$node];
        $sitemapNodes         = [$node];
        $imageMapperNodeList  = [$node];
        $sitemapNodeById      = ['primary' => $node];
        $heatmapNodeCollection = [$node];
    }
}
