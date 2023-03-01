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

    public function testGetPostBuildTasks()
    {
        $this->assertSame([], $this->service->getPostBuildTasks());
    }

    public function testAddPostBuildTask()
    {
        $this->service->addPostBuildTask(PostBuildTaskTestClass::class);
        $this->assertSame(['post-build-task-test-class' => PostBuildTaskTestClass::class], $this->service->getPostBuildTasks());
    }

    public function testSetOutputWithNull()
    {
        $this->assertInstanceOf(BuildTaskService::class, $this->service->setOutput(null));
    }

    public function testSetOutputWithOutputStyle()
    {
        $this->assertInstanceOf(BuildTaskService::class, $this->service->setOutput(Mockery::mock(OutputStyle::class)));
    }

    public function testSetOutputReturnsStatic()
    {
        $this->assertSame($this->service, $this->service->setOutput(null));
    }
}

class PostBuildTaskTestClass extends BuildTask
{
    public function run(): void
    {
        //
    }
}
