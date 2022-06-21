<?php

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\BuildHookService
 * @covers \Hyde\Framework\Contracts\AbstractBuildTask
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateSitemap
 */
class BuildHookServiceTest extends TestCase
{
    /**
     * @covers \Hyde\Framework\Commands\HydeBuildStaticSiteCommand::runPostBuildActions
     */
    public function test_build_command_can_run_post_build_tasks()
    {
        $this->artisan('build')
            ->expectsOutputToContain('Generating sitemap')
            ->expectsOutputToContain('Created sitemap.xml')
            ->assertExitCode(0);
    }
}
