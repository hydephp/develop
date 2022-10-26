<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * Class HydeGetBasePathHasFallbackTest.
 *
 * @covers \Hyde\Framework\HydeKernel::getBasePath
 */
class HydeGetBasePathHasFallbackTest extends TestCase
{
    public function test_hyde_get_base_path_falls_back_to_getcwd()
    {
        $mock = new class extends Hyde
        {
            public static string $basePath;
        };
        $this->assertEquals(getcwd(), $mock::getBasePath());
    }
}
