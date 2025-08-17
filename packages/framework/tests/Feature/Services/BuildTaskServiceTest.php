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
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @see \Hyde\Framework\Testing\Unit\BuildTaskServiceUnitTest
 */
#[CoversClass('\\Hyde\\Framework\\Services\\BuildTaskService')]
#[CoversClass('\\Hyde\\Framework\\Features\\BuildTasks\\BuildTask')]
#[CoversClass('\\Hyde\\Framework\\Features\\BuildTasks\\PreBuildTask')]
#[CoversClass('\\Hyde\\Framework\\Features\\BuildTasks\\PostBuildTask')]
#[CoversClass('\\Hyde\\Framework\\Actions\\PostBuildTasks\\GenerateSitemap')]
#[CoversClass('\\Hyde\\Framework\\Actions\\PostBuildTasks\\GenerateRssFeed')]
class BuildTaskServiceTest extends TestCase
{
    /**
     * @see \Hyde\Framework\Testing\Unit\BuildTaskServiceUnitTest
     */
    public function testBuildCommandCanRunBuildTasks()
    {
        $this->withSiteUrl();

        $this->artisan('build')
            ->expectsOutputToContain('Removing all files from build directory')
            ->expectsOutputToContain('Generating sitemap')
            ->expectsOutputToContain('Created _site/sitemap.xml')
            ->assertExitCode(0);

        File::cleanDirectory(Hyde::path('_site'));
    }

    public function testRunPostBuildTasksRunsConfiguredTasksDoesNothingIfNoTasksAreConfigured()
    {
        $service = $this->makeService();
        $service->runPostBuildTasks();

        $this->expectOutputString('');
    }

    public function testGetPostBuildTasksReturnsArrayMergedWithConfig()
    {
        config(['hyde.build_tasks' => [SecondBuildTask::class]]);

        $service = $this->makeService();
        $tasks = $service->getRegisteredTasks();

        $this->assertSame(1, count(array_keys($tasks, SecondBuildTask::class)));
    }

    public function testGetPostBuildTasksMergesDuplicateKeys()
    {
        app(BuildTaskService::class)->registerTask(TestBuildTask::class);
        config(['hyde.build_tasks' => [TestBuildTask::class]]);

        $service = $this->makeService();
        $tasks = $service->getRegisteredTasks();

        $this->assertSame(1, count(array_keys($tasks, TestBuildTask::class)));
    }

    public function testRunPostBuildTasksRunsConfiguredTasks()
    {
        $task = $this->makeTask();

        app(BuildTaskService::class)->registerTask(get_class($task));

        $service = $this->makeService();
        $service->runPostBuildTasks();

        $this->expectOutputString('BuildTask');
    }

    public function testExceptionHandlerShowsErrorMessageAndExitsWithCode1WithoutThrowingException()
    {
        $this->assertSame(1, (new class extends BuildTask
        {
            public function handle(): void
            {
                throw new Exception('foo', 1);
            }
        })->run());
    }

    public function testFindTasksInAppDirectoryMethodDiscoversTasksInAppDirectory()
    {
        $this->directory('app/Actions');
        $this->file('app/Actions/FooBuildTask.php', $this->classFileStub());

        $this->assertContains('App\Actions\FooBuildTask', (new BuildTaskService())->getRegisteredTasks());
    }

    public function testAutomaticallyDiscoveredTasksCanBeExecuted()
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
