<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Services\BuildTaskService;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Services\BuildTaskService
 *
 * @see \Hyde\Framework\Testing\Feature\Services\BuildTaskServiceTest
 */
class BuildTaskServiceUnitTest extends UnitTestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(BuildTaskService::class, new BuildTaskService());
    }

    protected function service(): BuildTaskService
    {
        return new BuildTaskService();
    }
}
