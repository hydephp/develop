<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;

/**
 * High level test for the feature that allows navigation items to be sorted by filename prefix.
 *
 * The feature can be disabled in the config. It also works within sidebar groups,
 * so that multiple groups can have the same prefix independent of other groups.
 *
 * @covers \Hyde\Framework\Features\Navigation\MainNavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\DocumentationSidebar
 */
class FilenamePrefixNavigationPriorityTest extends TestCase
{
    protected function fixtureFlatMain(): array
    {
        return [
            //
        ];
    }

    protected function fixtureGroupedMain(): array
    {
        return [
            //
        ];
    }

    protected function fixtureFlatSidebar(): array
    {
        return [
            //
        ];
    }

    protected function fixtureGroupedSidebar(): array
    {
        return [
            //
        ];
    }
}
