<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Framework\Features\BuildTasks\BuildTask;

/**
 * @covers \Hyde\Framework\Features\BuildTasks\BuildTask
 */
class BuildTaskUnitTest extends UnitTestCase
{
    public function testCanCreateBuildTask()
    {
        $task = new class extends BuildTask
        {
            public function handle(): void
            {
                // Do nothing
            }
        };

        $this->assertInstanceOf(BuildTask::class, $task);
    }
}
