<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Testing\TestCase;

class AllPageTypesHaveUnitTestsTest extends TestCase
{
    public function testAllPageTypesHaveUnitTests()
    {
        $pages = glob(__DIR__ . '/../../../src/Pages/*Page.php');
        $this->assertNotEmpty($pages);

        foreach ($pages as $page) {
            $page = basename($page, '.php');
            $test = __DIR__ . "/{$page}UnitTest.php";

            $this->assertFileExists($test, "Missing unit test for {$page}");
        }
    }
}
