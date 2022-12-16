<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\BuildSitemapCommand
 * @covers \Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateSitemap
 */
class BuildSitemapCommandTest extends TestCase
{
    public function test_sitemap_is_generated_when_conditions_are_met()
    {
        config(['site.url' => 'https://example.com']);
        config(['site.generate_sitemap' => true]);

        $this->unlinkIfExists(Hyde::path('_site/sitemap.xml'));
        $this->artisan('build:sitemap')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/sitemap.xml'));
        unlink(Hyde::path('_site/sitemap.xml'));
    }
}
