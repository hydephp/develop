<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Services\BuildTaskService;
use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Testing\UnitTestCase;
use Illuminate\Console\OutputStyle;
use Mockery;

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
        $this->service = new BuildTaskService();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(BuildTaskService::class, new BuildTaskService());
    }

    public function testGetTasks()
    {
        $this->assertSame([], $this->service->getTasks());
    }

    public function testRegisterTask()
    {
        $this->service->registerTask(PostBuildTaskTestClass::class);
        $this->assertSame([PostBuildTaskTestClass::class], $this->service->getTasks());
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
}

class PostBuildTaskTestClass extends BuildTask
{
    public function run(): void
    {
        //
    }
}
