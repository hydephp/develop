<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Services\BuildTaskService;
use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateSitemap as FrameworkGenerateSitemap;
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
        self::mockConfig();
    }

    protected function setUp(): void
    {
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
        $this->createService();

        $this->assertSame([TestBuildTask::class], $this->service->getTasks());
    }

    public function testRegisterTask()
    {
        $this->service->registerTask(TestBuildTask::class);
        $this->assertSame([TestBuildTask::class], $this->service->getTasks());
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

        self::mockConfig();
    }

    public function testRegisterTaskWithInvalidClassType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BuildTask [stdClass] must extend the HydeBuildTask class.');

        $this->service->registerTask(stdClass::class);
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

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::registerIf
     */
    public function testRegisterIfRegistersTaskIfSuppliedBooleanIsTrue()
    {
        $this->service->registerIf(TestBuildTask::class, true);
        $this->assertSame([TestBuildTask::class], $this->service->getTasks());
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::registerIf
     */
    public function testRegisterIfDoesNotRegisterTaskIfSuppliedBooleanIsFalse()
    {
        $this->service->registerIf(TestBuildTask::class, false);
        $this->assertSame([], $this->service->getTasks());
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::registerIf
     */
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

    protected function markTestSuccessful(): void
    {
        $this->assertTrue(true);
    }

    protected function createService(): void
    {
        $this->service = new BuildTaskService();
    }
}

class TestBuildTask extends BuildTask
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
