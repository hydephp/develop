<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Services\BuildTaskService;
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

    public function testConstruct()
    {
        $this->assertInstanceOf(BuildTaskService::class, new BuildTaskService());
    }

    public function testGetPostBuildTasks()
    {
        $this->assertIsArray($this->service()->getPostBuildTasks());
        $this->assertSame([], $this->service()->getPostBuildTasks());
    }

    public function testSetOutputWithNull()
    {
        $this->assertInstanceOf(BuildTaskService::class, $this->service()->setOutput(null));
    }

    public function testSetOutputWithOutputStyle()
    {
        $this->assertInstanceOf(BuildTaskService::class, $this->service()->setOutput(Mockery::mock(OutputStyle::class)));
    }

    public function testSetOutputReturnsStatic()
    {
        $this->assertSame($service = $this->service(), $service->setOutput(null));
    }

    protected function service(): BuildTaskService
    {
        return new BuildTaskService();
    }
}
