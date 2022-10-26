<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\HydeKernel;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * Class HydePathHelperTest.
 *
 * @covers \Hyde\Framework\HydeKernel::path
 */
class HydePathHelperTest extends TestCase
{
    public function test_method_exists()
    {
        $this->assertTrue(method_exists(HydeKernel::class, 'path'));
    }

    public function test_string_is_returned()
    {
        $this->assertIsString(Hyde::path());
    }

    public function test_returned_directory_contains_content_expected_to_be_in_the_project_directory()
    {
        $this->assertTrue(
            file_exists(Hyde::path().DIRECTORY_SEPARATOR.'hyde') &&
                file_exists(Hyde::path().DIRECTORY_SEPARATOR.'_pages') &&
                file_exists(Hyde::path().DIRECTORY_SEPARATOR.'_posts') &&
                file_exists(Hyde::path().DIRECTORY_SEPARATOR.'_site')
        );
    }
}
