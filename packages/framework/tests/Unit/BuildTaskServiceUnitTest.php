<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\Filesystem;
use Hyde\Framework\Actions\PostBuildTasks\GenerateBuildManifest;
use Hyde\Framework\Actions\PostBuildTasks\GenerateRssFeed;
use Hyde\Framework\Actions\PostBuildTasks\GenerateSearch;
use Hyde\Framework\Actions\PostBuildTasks\GenerateSitemap as FrameworkGenerateSitemap;
use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Features\BuildTasks\PostBuildTask;
use Hyde\Framework\Features\BuildTasks\PreBuildTask;
use Hyde\Framework\Services\BuildTaskService;
use Hyde\Testing\UnitTestCase;
use Illuminate\Console\OutputStyle;
use Mockery;
use ReflectionClass;
use stdClass;
use TypeError;

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
        self::mockConfig(['hyde' => [
            'empty_output_directory' => false,
            'generate_build_manifest' => false,
        ]]);
        $this->createService();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(BuildTaskService::class, new BuildTaskService());
    }

    public function testGetTasks()
    {
        $this->assertSame([], $this->service->getRegisteredTasks());
    }

    public function testGetTasksWithTaskRegisteredInConfig()
    {
        self::mockConfig(array_merge(config()->all(), ['hyde.build_tasks' => [TestBuildTask::class]]));
        $this->assertSame([TestBuildTask::class], $this->createService()->getRegisteredTasks());
    }

    public function testRegisterTask()
    {
        $this->service->registerTask(TestBuildTask::class);
        $this->assertSame([TestBuildTask::class], $this->service->getRegisteredTasks());
    }

    public function testRegisterPreBuildTask()
    {
        $this->service->registerTask(TestPreBuildTask::class);
        $this->assertSame([TestPreBuildTask::class], $this->service->getRegisteredTasks());
    }

    public function testRegisterPostBuildTask()
    {
        $this->service->registerTask(TestPostBuildTask::class);
        $this->assertSame([TestPostBuildTask::class], $this->service->getRegisteredTasks());
    }

    public function testRegisterInstantiatedTask()
    {
        $this->service->registerTask(new TestBuildTask());
        $this->assertSame([TestBuildTask::class], $this->service->getRegisteredTasks());
    }

    public function testRegisterInstantiatedPreBuildTask()
    {
        $this->service->registerTask(new TestPreBuildTask());
        $this->assertSame([TestPreBuildTask::class], $this->service->getRegisteredTasks());
    }

    public function testRegisterInstantiatedPostBuildTask()
    {
        $this->service->registerTask(new TestPostBuildTask());
        $this->assertSame([TestPostBuildTask::class], $this->service->getRegisteredTasks());
    }

    public function testRegisterTaskWithInvalidClassTypeThrowsException()
    {
        $this->expectException(TypeError::class);
        $this->service->registerTask(stdClass::class);
    }

    public function testRegisterTaskWithoutChildExtensionThrowsException()
    {
        $this->expectException(TypeError::class);
        $this->service->registerTask(TestBuildTaskNotExtendingChildren::class);
    }

    public function testRegisterTaskWithBaseClassThrowsException()
    {
        $this->expectException(TypeError::class);
        $this->service->registerTask(InstantiableTestBuildTask::class);
    }

    public function testRegisterTaskWithAlreadyRegisteredTask()
    {
        $this->service->registerTask(TestBuildTask::class);
        $this->service->registerTask(TestBuildTask::class);

        $this->assertSame([TestBuildTask::class], $this->service->getRegisteredTasks());
    }

    public function testRegisterTaskWithTaskAlreadyRegisteredInConfig()
    {
        self::mockConfig(array_merge(config()->all(), ['hyde.build_tasks' => [TestBuildTask::class]]));
        $this->createService();

        $this->service->registerTask(TestBuildTask::class);
        $this->assertSame([TestBuildTask::class], $this->service->getRegisteredTasks());
    }

    public function testCanRegisterFrameworkTasks()
    {
        $this->service->registerTask(FrameworkGenerateSitemap::class);
        $this->assertSame([FrameworkGenerateSitemap::class], $this->service->getRegisteredTasks());
    }

    public function testCanOverloadFrameworkTasks()
    {
        $this->service->registerTask(FrameworkGenerateSitemap::class);
        $this->service->registerTask(GenerateSitemap::class);

        $this->assertSame([GenerateSitemap::class], $this->service->getRegisteredTasks());
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

    public function testGenerateBuildManifestExtendsPostBuildTask()
    {
        $this->assertInstanceOf(PostBuildTask::class, new GenerateBuildManifest());
    }

    public function testGenerateRssFeedExtendsPostBuildTask()
    {
        $this->assertInstanceOf(PostBuildTask::class, new GenerateRssFeed());
    }

    public function testGenerateSearchExtendsPostBuildTask()
    {
        $this->assertInstanceOf(PostBuildTask::class, new GenerateSearch());
    }

    public function testGenerateSitemapExtendsPostBuildTask()
    {
        $this->assertInstanceOf(PostBuildTask::class, new FrameworkGenerateSitemap());
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

    public function testRunPreBuildTasksCallsHandleMethods()
    {
        $task = Mockery::mock(TestPreBuildTask::class)->makePartial()->shouldReceive('handle')->once()->getMock();
        $this->service->registerTask($task);
        $this->service->runPreBuildTasks();
        $this->verifyMockeryExpectations();
    }

    public function testRunPostBuildTasksCallsHandleMethods()
    {
        $task = Mockery::mock(TestPostBuildTask::class)->makePartial()->shouldReceive('handle')->once()->getMock();
        $this->service->registerTask($task);
        $this->service->runPostBuildTasks();
        $this->verifyMockeryExpectations();
    }

    public function testRunPreBuildTasksCallsRunMethods()
    {
        $task = Mockery::mock(TestPreBuildTask::class)->makePartial()->shouldReceive('run')->once()->getMock();
        $this->service->registerTask($task);
        $this->service->runPreBuildTasks();
        $this->verifyMockeryExpectations();
    }

    public function testRunPostBuildTasksCallsRunMethods()
    {
        $task = Mockery::mock(TestPostBuildTask::class)->makePartial()->shouldReceive('run')->once()->getMock();
        $this->service->registerTask($task);
        $this->service->runPostBuildTasks();
        $this->verifyMockeryExpectations();
    }

    public function testRunPreBuildTasksCallsRunMethodsWithNullWhenServiceHasNoOutput()
    {
        $task = Mockery::mock(TestPreBuildTask::class)->makePartial()->shouldReceive('run')->with(null)->once()->getMock();
        $this->service->registerTask($task);
        $this->service->runPreBuildTasks();
        $this->verifyMockeryExpectations();
    }

    public function testRunPostBuildTasksCallsRunMethodsWithNullWhenServiceHasNoOutput()
    {
        $task = Mockery::mock(TestPostBuildTask::class)->makePartial()->shouldReceive('run')->with(null)->once()->getMock();
        $this->service->registerTask($task);
        $this->service->runPostBuildTasks();
        $this->verifyMockeryExpectations();
    }

    public function testRunPreBuildTasksCallsRunMethodsWithOutputWhenServiceHasOutput()
    {
        $output = Mockery::mock(OutputStyle::class)->makePartial();
        $task = Mockery::mock(TestPreBuildTask::class)->makePartial()->shouldReceive('run')->with($output)->once()->getMock();
        $this->service->setOutput($output);
        $this->service->registerTask($task);
        $this->service->runPreBuildTasks();
        $this->verifyMockeryExpectations();
    }

    public function testRunPostBuildTasksCallsRunMethodsWithOutputWhenServiceHasOutput()
    {
        $output = Mockery::mock(OutputStyle::class)->makePartial();
        $task = Mockery::mock(TestPostBuildTask::class)->makePartial()->shouldReceive('run')->with($output)->once()->getMock();
        $this->service->setOutput($output);
        $this->service->registerTask($task);
        $this->service->runPostBuildTasks();
        $this->verifyMockeryExpectations();
    }

    public function testServiceSearchesForTasksInAppDirectory()
    {
        $kernel = HydeKernel::getInstance();
        $filesystem = Mockery::mock(Filesystem::class, [$kernel])
            ->makePartial()->shouldReceive('smartGlob')->once()
            ->with('app/Actions/*BuildTask.php', 0)
            ->andReturn(collect())->getMock();

        // Inject mock into Kernel (No better way to do this at the moment)
        (new ReflectionClass($kernel))->getProperty('filesystem')->setValue($kernel, $filesystem);

        $this->createService();
        $this->verifyMockeryExpectations();
        self::setupKernel();
    }

    public function testServiceFindsTasksInAppDirectory()
    {
        $kernel = HydeKernel::getInstance();
        $filesystem = Mockery::mock(Filesystem::class, [$kernel])->makePartial()
            ->shouldReceive('smartGlob')->once()
            ->with('app/Actions/*BuildTask.php', 0)
            ->andReturn(collect([/** TODO */]))->getMock();

        // Inject mock into Kernel (No better way to do this at the moment)
        (new ReflectionClass($kernel))->getProperty('filesystem')->setValue($kernel, $filesystem);

        $this->createService();
        $this->verifyMockeryExpectations();
        self::setupKernel();
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

    protected function verifyMockeryExpectations(): void
    {
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
        Mockery::close();
    }
}

class InstantiableTestBuildTask extends BuildTask
{
    public function handle(): void
    {
        //
    }
}

class TestBuildTask extends PostBuildTask
{
    public function handle(): void
    {
        //
    }
}

class TestPreBuildTask extends PreBuildTask
{
    public function handle(): void
    {
        //
    }
}

class TestPostBuildTask extends PostBuildTask
{
    public function handle(): void
    {
        //
    }
}

class TestBuildTaskNotExtendingChildren extends BuildTask
{
    public function handle(): void
    {
        //
    }
}

/** Test class to test overloading */
class GenerateSitemap extends FrameworkGenerateSitemap
{
    public function handle(): void
    {
        //
    }
}
