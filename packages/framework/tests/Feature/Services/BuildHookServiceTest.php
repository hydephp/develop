<?php

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Contracts\AbstractBuildTask;
use Hyde\Framework\Services\BuildHookService;
use Hyde\Testing\TestCase;
use Illuminate\Console\OutputStyle;
use Mockery;
use Symfony\Component\Console\Input\ArrayInput;

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

    /**
     * @covers \Hyde\Framework\Services\BuildHookService::runPostBuildTasks
     */
    public function test_run_post_build_tasks_runs_configured_tasks_does_nothing_if_no_tasks_are_configured()
    {
        BuildHookService::$postBuildTasks = [];

        $service = $this->makeService();
        $service->runPostBuildTasks();

        $this->expectOutputString('');
    }

    /**
     * @covers \Hyde\Framework\Services\BuildHookService::getPostBuildTasks
     */
    public function test_get_post_build_tasks_returns_array_merged_with_config()
    {
        BuildHookService::$postBuildTasks = ['foo'];
        config(['hyde.post_build_tasks' => ['bar']]);

        $service = $this->makeService();
        $this->assertEquals(['bar', 'foo'], $service->getPostBuildTasks());
    }

    /**
     * @covers \Hyde\Framework\Services\BuildHookService::getPostBuildTasks
     */
    public function test_get_post_build_tasks_merges_duplicate_keys()
    {
        BuildHookService::$postBuildTasks = ['foo'];
        config(['hyde.post_build_tasks' => ['foo']]);

        $service = $this->makeService();
        $this->assertEquals(['foo'], $service->getPostBuildTasks());
    }

    /**
     * @covers \Hyde\Framework\Services\BuildHookService::runPostBuildTasks
     */
    public function test_run_post_build_tasks_runs_configured_tasks()
    {
        $task = $this->makeTask();

        BuildHookService::$postBuildTasks = [get_class($task)];

        $service = $this->makeService();
        $service->runPostBuildTasks();

        $this->expectOutputString('AbstractBuildTask');
    }

    /**
     * @covers \Hyde\Framework\Services\BuildHookService::run
     */
    public function test_run_method_runs_task_by_class_name_input_and_returns_self()
    {
        $task = $this->makeTask();

        $service = $this->makeService();
        $return = $service->run(get_class($task));

        $this->expectOutputString('AbstractBuildTask');

        $this->assertSame($service, $return);
    }

    /**
     * @covers \Hyde\Framework\Services\BuildHookService::runIf
     */
    public function test_run_if_runs_task_if_supplied_boolean_is_true()
    {
        $task = $this->makeTask();

        $service = $this->makeService();
        $return = $service->runIf(get_class($task), true);

        $this->expectOutputString('AbstractBuildTask');

        $this->assertSame($service, $return);
    }

    /**
     * @covers \Hyde\Framework\Services\BuildHookService::runIf
     */
    public function test_run_if_does_not_run_task_if_supplied_boolean_is_false()
    {
        $task = $this->makeTask();

        $service = $this->makeService();
        $return = $service->runIf(get_class($task), false);

        $this->expectOutputString('');

        $this->assertSame($service, $return);
    }

    /**
     * @covers \Hyde\Framework\Services\BuildHookService::runIf
     */
    public function test_run_if_runs_task_if_supplied_callable_returns_true()
    {
        $task = $this->makeTask();

        $service = $this->makeService();
        $return = $service->runIf(get_class($task), function () {
            return true;
        });

        $this->expectOutputString('AbstractBuildTask');

        $this->assertSame($service, $return);
    }

    /**
     * @covers \Hyde\Framework\Services\BuildHookService::runIf
     */
    public function test_run_if_does_not_run_task_if_supplied_callable_returns_false()
    {
        $task = $this->makeTask();

        $service = $this->makeService();
        $return = $service->runIf(get_class($task), function () {
            return false;
        });

        $this->expectOutputString('');

        $this->assertSame($service, $return);
    }

    protected function makeService(): BuildHookService
    {
        return new BuildHookService();
    }

    protected function makeTask(): AbstractBuildTask
    {
        return new class extends AbstractBuildTask
        {
            public function run(): void
            {
                echo 'AbstractBuildTask';
            }
        };
    }
}
