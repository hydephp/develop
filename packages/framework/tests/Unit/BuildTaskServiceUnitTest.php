<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\Filesystem;
use Hyde\Framework\Services\BuildTaskService;
use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateSitemap as FrameworkGenerateSitemap;
use Hyde\Framework\Features\BuildTasks\PostBuildTasks;
use Hyde\Framework\Features\BuildTasks\Contracts\RunsAfterBuild;
use Hyde\Framework\Features\BuildTasks\Contracts\RunsBeforeBuild;
use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Illuminate\Console\OutputStyle;
use InvalidArgumentException;
use Mockery;
use ReflectionClass;
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
        $this->assertSame([], $this->service->getRegisteredTasks());
    }

    public function testGetTasksWithTaskRegisteredInConfig()
    {
        self::mockConfig(['hyde.build_tasks' => [TestBuildTask::class]]);
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
        $this->expectException(InvalidArgumentException::class);
        $this->service->registerTask(stdClass::class);
    }

    public function testRegisterTaskWithInvalidClassTypeExceptionMessageIsHelpful()
    {
        $this->expectExceptionMessage('BuildTask [stdClass] must extend the HydeBuildTask class.');
        $this->service->registerTask(stdClass::class);
    }

    public function testRegisterTaskWithoutRunnerInterfaceImplementationThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->registerTask(TestBuildTaskWithoutRunnerInterface::class);
    }

    public function testRegisterTaskWithoutRunnerInterfaceImplementationExceptionMessageIsHelpful()
    {
        $this->expectExceptionMessage('BuildTask ['.TestBuildTaskWithoutRunnerInterface::class.'] must implement either the RunsBeforeBuild or RunsAfterBuild interface.');
        $this->service->registerTask(TestBuildTaskWithoutRunnerInterface::class);
    }

    public function testRegisterTaskWithAlreadyRegisteredTask()
    {
        $this->service->registerTask(TestBuildTask::class);
        $this->service->registerTask(TestBuildTask::class);

        $this->assertSame([TestBuildTask::class], $this->service->getRegisteredTasks());
    }

    public function testRegisterTaskWithTaskAlreadyRegisteredInConfig()
    {
        self::mockConfig(['hyde.build_tasks' => [TestBuildTask::class]]);
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

    public function testRegisterIfRegistersTaskIfSuppliedBooleanIsTrue()
    {
        $this->service->registerIf(TestBuildTask::class, true);
        $this->assertSame([TestBuildTask::class], $this->service->getRegisteredTasks());
    }

    public function testRegisterIfDoesNotRegisterTaskIfSuppliedBooleanIsFalse()
    {
        $this->service->registerIf(TestBuildTask::class, false);
        $this->assertSame([], $this->service->getRegisteredTasks());
    }

    public function testRegisterIfRegistersTaskIfSuppliedCallableReturnsTrue()
    {
        $this->service->registerIf(TestBuildTask::class, fn () => true);
        $this->assertSame([TestBuildTask::class], $this->service->getRegisteredTasks());
    }

    public function testRegisterIfDoesNotRunTaskIfSuppliedCallableReturnsFalse()
    {
        $this->service->registerIf(TestBuildTask::class, fn () => false);
        $this->assertSame([], $this->service->getRegisteredTasks());
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
        $filesystem = Mockery::mock(Filesystem::class, [HydeKernel::getInstance()])->makePartial()->shouldReceive('smartGlob')->once()->with('app/Actions/*BuildTask.php', 0)->andReturn(collect())->getMock();

        // No better way to do this at the moment
        $reflector = new ReflectionClass(HydeKernel::class);
        $property = $reflector->getProperty('filesystem');
        $property->setValue(HydeKernel::getInstance(), $filesystem);

        $this->createService();
        $this->verifyMockeryExpectations();
    }

    public function testServiceFindsTasksInAppDirectory()
    {
        $filesystem = Mockery::mock(Filesystem::class, [HydeKernel::getInstance()])->makePartial()->shouldReceive('smartGlob')->once()->with('app/Actions/*BuildTask.php', 0)->andReturn(collect([/** TODO */]))->getMock();

        // No better way to do this at the moment
        $reflector = new ReflectionClass(HydeKernel::class);
        $property = $reflector->getProperty('filesystem');
        $property->setValue(HydeKernel::getInstance(), $filesystem);

        $this->createService();
        $this->verifyMockeryExpectations();
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

class TestBuildTaskWithoutRunnerInterface extends BuildTask
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
