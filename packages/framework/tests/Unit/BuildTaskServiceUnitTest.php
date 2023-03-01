<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Services\BuildTaskService;
use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateSitemap as FrameworkGenerateSitemap;
use Hyde\Framework\Features\BuildTasks\PostBuildTasks;
use Hyde\Framework\Features\BuildTasks\Contracts\RunsAfterBuild;
use Hyde\Framework\Features\BuildTasks\Contracts\RunsBeforeBuild;
use Hyde\Testing\UnitTestCase;
use Illuminate\Console\OutputStyle;
use InvalidArgumentException;
use Mockery;
use stdClass;

/**
 * @covers \Hyde\Framework\Services\BuildTaskService
 *
 * @see \Hyde\Framework\Testing\Feature\Services\BuildTaskServiceTest
 */
class BuildTaskServiceUnitTest extends UnitTestCase
{
    protected BuildTaskService $service;

    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
    }

    protected function setUp(): void
    {
        self::mockConfig();
        $this->createService();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(BuildTaskService::class, new BuildTaskService());
    }

    public function testGetTasks()
    {
        $this->assertSame([], $this->service->getTasks());
    }

    public function testGetTasksWithTaskRegisteredInConfig()
    {
        self::mockConfig(['hyde.build_tasks' => [TestBuildTask::class]]);
        $this->assertSame([TestBuildTask::class], $this->createService()->getTasks());
    }

    public function testRegisterTask()
    {
        $this->service->registerTask(TestBuildTask::class);
        $this->assertSame([TestBuildTask::class], $this->service->getTasks());
    }

    public function testRegisterPreBuildTask()
    {
        $this->service->registerTask(TestPreBuildTask::class);
        $this->assertSame([TestPreBuildTask::class], $this->service->getTasks());
    }

    public function testRegisterPostBuildTask()
    {
        $this->service->registerTask(TestPostBuildTask::class);
        $this->assertSame([TestPostBuildTask::class], $this->service->getTasks());
    }

    public function testRegisterTaskWithInvalidClassTypeThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->registerTask(stdClass::class);
    }

    public function testRegisterTaskWithInvalidClassTypeExceptionMessageIsHelpful()
    {
        $this->expectExceptionMessage('BuildTask [stdClass] must extend the HydeBuildTask class.');
        $this->service->registerTask(stdClass::class);
    }

    public function testRegisterTaskWithAlreadyRegisteredTask()
    {
        $this->service->registerTask(TestBuildTask::class);
        $this->service->registerTask(TestBuildTask::class);

        $this->assertSame([TestBuildTask::class], $this->service->getTasks());
    }

    public function testRegisterTaskWithTaskAlreadyRegisteredInConfig()
    {
        self::mockConfig(['hyde.build_tasks' => [TestBuildTask::class]]);
        $this->createService();

        $this->service->registerTask(TestBuildTask::class);
        $this->assertSame([TestBuildTask::class], $this->service->getTasks());
    }

    public function testCanRegisterFrameworkTasks()
    {
        $this->service->registerTask(FrameworkGenerateSitemap::class);
        $this->assertSame([FrameworkGenerateSitemap::class], $this->service->getTasks());
    }

    public function testCanOverloadFrameworkTasks()
    {
        $this->service->registerTask(FrameworkGenerateSitemap::class);
        $this->service->registerTask(GenerateSitemap::class);

        $this->assertSame([GenerateSitemap::class], $this->service->getTasks());
    }

    public function testRegisterIfRegistersTaskIfSuppliedBooleanIsTrue()
    {
        $this->service->registerIf(TestBuildTask::class, true);
        $this->assertSame([TestBuildTask::class], $this->service->getTasks());
    }

    public function testRegisterIfDoesNotRegisterTaskIfSuppliedBooleanIsFalse()
    {
        $this->service->registerIf(TestBuildTask::class, false);
        $this->assertSame([], $this->service->getTasks());
    }

    public function testRegisterIfRegistersTaskIfSuppliedCallableReturnsTrue()
    {
        $this->service->registerIf(TestBuildTask::class, fn () => true);
        $this->assertSame([TestBuildTask::class], $this->service->getTasks());
    }

    public function testRegisterIfDoesNotRunTaskIfSuppliedCallableReturnsFalse()
    {
        $this->service->registerIf(TestBuildTask::class, fn () => false);
        $this->assertSame([], $this->service->getTasks());
    }

    public function testSetOutputWithNull()
    {
        $this->service->setOutput(null);
        $this->markTestSuccessful();
    }

    public function testSetOutputWithOutputStyle()
    {
        $this->service->setOutput(Mockery::mock(OutputStyle::class));
        $this->markTestSuccessful();
    }

    public function testGenerateBuildManifestImplementsRunsAfterBuild()
    {
        $this->assertInstanceOf(RunsAfterBuild::class, new PostBuildTasks\GenerateBuildManifest());
    }

    public function testGenerateRssFeedImplementsRunsAfterBuild()
    {
        $this->assertInstanceOf(RunsAfterBuild::class, new PostBuildTasks\GenerateRssFeed());
    }

    public function testGenerateSearchImplementsRunsAfterBuild()
    {
        $this->assertInstanceOf(RunsAfterBuild::class, new PostBuildTasks\GenerateSearch());
    }

    public function testGenerateSitemapImplementsRunsAfterBuild()
    {
        $this->assertInstanceOf(RunsAfterBuild::class, new PostBuildTasks\GenerateSitemap());
    }

    public function testRunPreBuildTasks()
    {
        $this->service->runPreBuildTasks();
        $this->markTestSuccessful();
    }

    public function testRunPostBuildTasks()
    {
        $this->service->runPostBuildTasks();
        $this->markTestSuccessful();
    }

    public function testRunPreBuildTasksWithTasks()
    {
        $this->service->registerTask(TestPreBuildTask::class);
        $this->service->runPreBuildTasks();
        $this->markTestSuccessful();
    }

    public function testRunPostBuildTasksWithTasks()
    {
        $this->service->registerTask(TestPostBuildTask::class);
        $this->service->runPostBuildTasks();
        $this->markTestSuccessful();
    }

    protected function markTestSuccessful(): void
    {
        $this->assertTrue(true);
    }

    protected function createService(): BuildTaskService
    {
        $this->service = new BuildTaskService();

        return $this->service;
    }
}

class TestBuildTask extends BuildTask implements RunsBeforeBuild, RunsAfterBuild
{
    public function handle(): void
    {
        //
    }
}

class TestPreBuildTask extends BuildTask implements RunsBeforeBuild
{
    public function handle(): void
    {
        //
    }
}

class TestPostBuildTask extends BuildTask implements RunsAfterBuild
{
    public function handle(): void
    {
        //
    }
}

class GenerateSitemap extends FrameworkGenerateSitemap
{
    public function handle(): void
    {
        //
    }
}
