<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\BuildSitemapCommand
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateSitemap
 */
class BuildSitemapCommandTest extends TestCase
{
    public function testSitemapIsGeneratedWhenConditionsAreMet()
    {
        config(['hyde.url' => 'https://example.com']);
        // config(['hyde.generate_sitemap' => true]); This is only applied when calling the main build command. If the user calls this command directly, it will always generate the sitemap as long as the URL is set.

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));

        $this->artisan('build:sitemap')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('_site/sitemap.xml'));

        Filesystem::unlink('_site/sitemap.xml');
    }

    public function testSitemapIsNotGeneratedWhenConditionsAreNotMet()
    {
        config(['hyde.url' => '']);

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));

        $this->artisan('build:sitemap')->assertExitCode(0);
        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));
    }
}
