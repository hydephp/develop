<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Concerns\AbstractBuildTask;
use Hyde\Framework\Services\BuildTaskService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Services\BuildTaskService
 * @covers \Hyde\Framework\Concerns\AbstractBuildTask
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateSitemap
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateRssFeed
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateSearch
 *
 * @backupStaticAttributes enabled
 */
class BuildTaskServiceTest extends TestCase
{
    /**
     * @covers \Hyde\Framework\Commands\HydeBuildSiteCommand::runPostBuildActions
     */
    public function test_build_command_can_run_post_build_tasks()
    {
        config(['site.url' => 'foo']);

        $this->artisan('build')
            ->expectsOutputToContain('Generating sitemap')
            ->expectsOutputToContain('Created _site/sitemap.xml')
            ->assertExitCode(0);

        File::cleanDirectory(Hyde::path('_site'));
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::runPostBuildTasks
     */
    public function test_run_post_build_tasks_runs_configured_tasks_does_nothing_if_no_tasks_are_configured()
    {
        BuildTaskService::$postBuildTasks = [];

        $service = $this->makeService();
        $service->runPostBuildTasks();

        $this->expectOutputString('');
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::getPostBuildTasks
     */
    public function test_get_post_build_tasks_returns_array_merged_with_config()
    {
        BuildTaskService::$postBuildTasks = ['foo'];
        config(['hyde.post_build_tasks' => ['bar']]);

        $service = $this->makeService();
        $this->assertEquals(['bar', 'foo'], $service->getPostBuildTasks());
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::getPostBuildTasks
     */
    public function test_get_post_build_tasks_merges_duplicate_keys()
    {
        BuildTaskService::$postBuildTasks = ['foo'];
        config(['hyde.post_build_tasks' => ['foo']]);

        $service = $this->makeService();
        $this->assertEquals(['foo'], $service->getPostBuildTasks());
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::runPostBuildTasks
     */
    public function test_run_post_build_tasks_runs_configured_tasks()
    {
        $task = $this->makeTask();

        BuildTaskService::$postBuildTasks = [get_class($task)];

        $service = $this->makeService();
        $service->runPostBuildTasks();

        $this->expectOutputString('AbstractBuildTask');
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::run
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
     * @covers \Hyde\Framework\Services\BuildTaskService::runIf
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
     * @covers \Hyde\Framework\Services\BuildTaskService::runIf
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
     * @covers \Hyde\Framework\Services\BuildTaskService::runIf
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
     * @covers \Hyde\Framework\Services\BuildTaskService::runIf
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

    public function test_exception_handler_shows_error_message_and_exits_with_code_1_without_throwing_exception()
    {
        $return = (new class extends AbstractBuildTask
        {
            public function run(): void
            {
                throw new \Exception('foo', 1);
            }
        })->handle();

        $this->assertEquals(1, $return);
    }

    public function test_find_tasks_in_app_directory_method_discovers_tasks_in_app_directory()
    {
        File::makeDirectory(Hyde::path('app/Actions'));
        Hyde::touch('app/Actions/FooBuildTask.php');

        $this->assertEquals(['App\Actions\FooBuildTask'], BuildTaskService::findTasksInAppDirectory());
        File::deleteDirectory(Hyde::path('app/Actions'));
    }

    public function test_automatically_discovered_tasks_can_be_executed()
    {
        File::makeDirectory(Hyde::path('app/Actions'));
        File::put(Hyde::path('app/Actions/FooBuildTask.php'), '<?php

namespace App\Actions;

use Hyde\Framework\Concerns\AbstractBuildTask;

class FooBuildTask extends AbstractBuildTask {
    public function run(): void {
        echo "FooBuildTask";
    }
}');

        $service = $this->makeService();
        $service->runPostBuildTasks();

        $this->expectOutputString('FooBuildTask');
        File::deleteDirectory(Hyde::path('app/Actions'));
    }

    protected function makeService(): BuildTaskService
    {
        return new BuildTaskService();
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
