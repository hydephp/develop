<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Exception;
use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Features\BuildTasks\PostBuildTask;
use Hyde\Framework\Services\BuildTaskService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Services\BuildTaskService
 * @covers \Hyde\Framework\Features\BuildTasks\BuildTask
 * @covers \Hyde\Framework\Features\BuildTasks\PreBuildTask
 * @covers \Hyde\Framework\Features\BuildTasks\PostBuildTask
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateSitemap
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateRssFeed
 *
 * @see \Hyde\Framework\Testing\Unit\BuildTaskServiceUnitTest
 */
class BuildTaskServiceTest extends TestCase
{
    /**
     * @covers \Hyde\Console\Commands\BuildSiteCommand::runPostBuildActions
     */
    public function test_build_command_can_run_build_tasks()
    {
        $this->artisan('build')
            ->expectsOutputToContain('Removing all files from build directory')
            ->expectsOutputToContain('Generating sitemap')
            ->expectsOutputToContain('Created _site/sitemap.xml')
            ->assertExitCode(0);

        File::cleanDirectory(Hyde::path('_site'));
    }

    public function test_run_post_build_tasks_runs_configured_tasks_does_nothing_if_no_tasks_are_configured()
    {
        $service = $this->makeService();
        $service->runPostBuildTasks();

        $this->expectOutputString('');
    }

    public function test_get_post_build_tasks_returns_array_merged_with_config()
    {
        config(['hyde.build_tasks' => [SecondBuildTask::class]]);

        $service = $this->makeService();
        $tasks = $service->getRegisteredTasks();

        $this->assertEquals(1, count(array_keys($tasks, SecondBuildTask::class)));
    }

    public function test_get_post_build_tasks_merges_duplicate_keys()
    {
        app(BuildTaskService::class)->registerTask(TestBuildTask::class);
        config(['hyde.build_tasks' => [TestBuildTask::class]]);

        $service = $this->makeService();
        $tasks = $service->getRegisteredTasks();

        $this->assertEquals(1, count(array_keys($tasks, TestBuildTask::class)));
    }

    public function test_run_post_build_tasks_runs_configured_tasks()
    {
        $task = $this->makeTask();

        app(BuildTaskService::class)->registerTask(get_class($task));

        $service = $this->makeService();
        $service->runPostBuildTasks();

        $this->expectOutputString('BuildTask');
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

        $this->assertContains('App\Actions\FooBuildTask', (new BuildTaskService())->getRegisteredTasks());
    }

    public function test_automatically_discovered_tasks_can_be_executed()
    {
        $this->directory('app/Actions');
        $this->file('app/Actions/FooBuildTask.php', $this->classFileStub());

        $service = $this->makeService();
        $service->runPostBuildTasks();

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

        use Hyde\Framework\Features\BuildTasks\PostBuildTask;

        class FooBuildTask extends PostBuildTask {
            public function handle(): void {
                echo "FooBuildTask";
            }
        }

        PHP;
    }
}

class TestBuildTask extends PostBuildTask
{
    public function handle(): void
    {
        echo 'BuildTask';
    }
}

class SecondBuildTask extends PostBuildTask
{
    public function handle(): void
    {
        echo 'SecondBuildTask';
    }
}
