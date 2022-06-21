<?php

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\BuildHookService
 * @covers \Hyde\Framework\Contracts\AbstractBuildTask
 */
class BuildHookServiceTest extends TestCase
{
    /**
     * @covers \Hyde\Framework\Commands\HydeBuildStaticSiteCommand::runPostBuildActions
     */
    public function test_build_command_can_run_post_build_tasks()
    {
        $this->artisan('build')->assertExitCode(0);
    }
}
