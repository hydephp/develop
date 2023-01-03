<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Hyde;
use Hyde\Testing\TestCase;

class TestAllPageTypesHaveUnitTestsTest extends TestCase
{
    public function testAllPageTypesHaveUnitTests()
    {
        $pages = glob(Hyde::vendorPath('/src/Pages/*.php'));
        $this->assertNotEmpty($pages);

        // Simple assertion to make sure we got the right directory
        $this->assertStringContainsString('BladePage.php', json_encode($pages));

        foreach ($pages as $page) {
            $page = basename($page, '.php');
            $test = __DIR__."/{$page}UnitTest.php";

            $this->assertFileExists($test, "Missing unit test for class '$page'");
        }
    }
}
