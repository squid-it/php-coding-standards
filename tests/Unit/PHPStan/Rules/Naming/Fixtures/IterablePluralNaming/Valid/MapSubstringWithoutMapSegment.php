<?php

declare(strict_types=1);

namespace IterablePluralNamingFixtures\Valid;

final class MapSubstringWithoutMapSegment
{
    public function run(Node $node): void
    {
        $mapperNodeList        = [$node];
        $heatmapNodeList       = [$node];
        $sitemapNodeList       = [$node];
        $imageMapperNodeList   = [$node];
        $sitemapPrimaryNodeList = ['primary' => $node];
        $heatmapCurrentNodeList = [$node];
    }
}
