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
        self::mockConfig(['hyde.build_tasks' => [PostBuildTaskTestClass::class]]);
        $this->createService();

        $this->assertSame([PostBuildTaskTestClass::class], $this->service->getTasks());
    }

    public function testRegisterTask()
    {
        $this->service->registerTask(PostBuildTaskTestClass::class);
        $this->assertSame([PostBuildTaskTestClass::class], $this->service->getTasks());
    }

    public function testRegisterTaskWithAlreadyRegisteredTask()
    {
        $this->service->registerTask(PostBuildTaskTestClass::class);
        $this->service->registerTask(PostBuildTaskTestClass::class);

        $this->assertSame([PostBuildTaskTestClass::class], $this->service->getTasks());
    }

    public function testRegisterTaskWithTaskAlreadyRegisteredInConfig()
    {
        self::mockConfig(['hyde.build_tasks' => [PostBuildTaskTestClass::class]]);
        $this->createService();

        $this->service->registerTask(PostBuildTaskTestClass::class);
        $this->assertSame([PostBuildTaskTestClass::class], $this->service->getTasks());

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
        $this->service->registerIf(PostBuildTaskTestClass::class, true);
        $this->assertSame([PostBuildTaskTestClass::class], $this->service->getTasks());
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::registerIf
     */
    public function testRegisterIfDoesNotRegisterTaskIfSuppliedBooleanIsFalse()
    {
        $this->service->registerIf(PostBuildTaskTestClass::class, false);
        $this->assertSame([], $this->service->getTasks());
    }

    /**
     * @covers \Hyde\Framework\Services\BuildTaskService::registerIf
     */
    public function testRegisterIfRegistersTaskIfSuppliedCallableReturnsTrue()
    {
        $this->service->registerIf(PostBuildTaskTestClass::class, fn() => true);
        $this->assertSame([PostBuildTaskTestClass::class], $this->service->getTasks());
    }

    public function testRegisterIfDoesNotRunTaskIfSuppliedCallableReturnsFalse()
    {
        $this->service->registerIf(PostBuildTaskTestClass::class, fn() => false);
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

class PostBuildTaskTestClass extends BuildTask
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
