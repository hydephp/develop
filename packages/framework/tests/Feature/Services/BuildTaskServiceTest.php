<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Exception;
use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Services\BuildTaskService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Services\BuildTaskService
 * @covers \Hyde\Framework\Features\BuildTasks\BuildTask
 * @covers \Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateSitemap
 * @covers \Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateRssFeed
 * @covers \Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateSearch
 *
 * @see \Hyde\Framework\Testing\Unit\BuildTaskServiceUnitTest
 */
class BuildTaskServiceTest extends TestCase
{
    /**
     * @covers \Hyde\Console\Commands\BuildSiteCommand::runPostBuildActions
     */
    public function test_build_command_can_run_post_build_tasks()
    {
        config(['hyde.url' => 'foo']);

        $this->artisan('build')
            ->expectsOutputToContain('Generating sitemap')
            ->expectsOutputToContain('Created _site/sitemap.xml')
            ->assertExitCode(0);

        File::cleanDirectory(Hyde::path('_site'));
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::runTasks
     */
    public function test_run_post_build_tasks_runs_configured_tasks_does_nothing_if_no_tasks_are_configured()
    {
        $service = $this->makeService();
        $service->runTasks();

        $this->expectOutputString('');
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::getTasks
     */
    public function test_get_post_build_tasks_returns_array_merged_with_config()
    {
        config(['hyde.build_tasks' => [SecondBuildTask::class]]);

        $service = $this->makeService();
        $service->registerTask(TestBuildTask::class);

        $this->assertEquals([SecondBuildTask::class, TestBuildTask::class], $service->getTasks());
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::getTasks
     */
    public function test_get_post_build_tasks_merges_duplicate_keys()
    {
        app(BuildTaskService::class)->registerTask(TestBuildTask::class);
        config(['hyde.build_tasks' => [TestBuildTask::class]]);

        $service = $this->makeService();
        $this->assertEquals([TestBuildTask::class], $service->getTasks());
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::runTasks
     */
    public function test_run_post_build_tasks_runs_configured_tasks()
    {
        $task = $this->makeTask();

        app(BuildTaskService::class)->registerTask(get_class($task));

        $service = $this->makeService();
        $service->runTasks();

        $this->expectOutputString('BuildTask');
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::registerIf
     */
    public function test_register_if_registers_task_if_supplied_boolean_is_true()
    {
        $task = $this->makeTask();
        $service = $this->makeService();

        $service->registerIf(get_class($task), true);
        $this->assertSame([TestBuildTask::class], $service->getTasks());
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::registerIf
     */
    public function test_register_if_does_not_register_task_if_supplied_boolean_is_false()
    {
        $task = $this->makeTask();
        $service = $this->makeService();

        $service->registerIf(get_class($task), false);

        $this->assertSame([], $service->getTasks());
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::registerIf
     */
    public function test_register_if_registers_task_if_supplied_callable_returns_true()
    {
        $task = $this->makeTask();
        $service = $this->makeService();

        $service->registerIf(get_class($task), function () {
            return true;
        });

        $this->assertSame([TestBuildTask::class], $service->getTasks());
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::registerIf
     */
    public function test_register_if_does_not_run_task_if_supplied_callable_returns_false()
    {
        $task = $this->makeTask();
        $service = $this->makeService();

        $service->registerIf(get_class($task), function () {
            return false;
        });

        $this->assertSame([], $service->getTasks());
    }

    public function test_exception_handler_shows_error_message_and_exits_with_code_1_without_throwing_exception()
    {
        $return = (new class extends BuildTask
        {
            public function handle(): void
            {
                throw new Exception('foo', 1);
            }
        })->run();

        $this->assertEquals(1, $return);
    }

    public function test_find_tasks_in_app_directory_method_discovers_tasks_in_app_directory()
    {
        $this->directory('app/Actions');
        $this->file('app/Actions/FooBuildTask.php', $this->classFileStub());

        $this->assertEquals(['App\Actions\FooBuildTask'], (new BuildTaskService())->getTasks());
    }

    public function test_automatically_discovered_tasks_can_be_executed()
    {
        $this->directory('app/Actions');
        $this->file('app/Actions/FooBuildTask.php', $this->classFileStub());

        $service = $this->makeService();
        $service->runTasks();

        $this->expectOutputString('FooBuildTask');
    }

    protected function makeService(): BuildTaskService
    {
        return app(BuildTaskService::class);
    }

    protected function makeTask(): BuildTask
    {
        return new TestBuildTask();
    }

    protected function classFileStub(): string
    {
        return <<<'PHP'
        <?php
        
        namespace App\Actions;
        
        use Hyde\Framework\Features\BuildTasks\BuildTask;
        
        class FooBuildTask extends BuildTask {
            public function handle(): void {
                echo "FooBuildTask";
            }
        }

        PHP;
    }
}

class TestBuildTask extends BuildTask
{
    public function handle(): void
    {
        echo 'BuildTask';
    }
}

class SecondBuildTask extends BuildTask
{
    public function handle(): void
    {
        echo 'SecondBuildTask';
    }
}
