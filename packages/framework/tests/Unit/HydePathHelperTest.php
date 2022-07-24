<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Framework\HydeKernel;
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

    public function test_method_returns_qualified_file_path_when_supplied_with_argument()
    {
        $this->assertEquals(Hyde::path('file.php'), Hyde::path().DIRECTORY_SEPARATOR.'file.php');
    }

    public function test_method_strips_trailing_directory_separators_from_argument()
    {
        $this->assertEquals(Hyde::path('\\/file.php/'), Hyde::path().DIRECTORY_SEPARATOR.'file.php');
    }

    public function test_method_returns_expected_value_for_nested_path_arguments()
    {
        $this->assertEquals(Hyde::path('directory/file.php'), Hyde::path().DIRECTORY_SEPARATOR.'directory/file.php');
    }

    public function test_method_returns_expected_value_regardless_of_trailing_directory_separators_in_argument()
    {
        $this->assertEquals(Hyde::path('directory/file.php/'), Hyde::path().DIRECTORY_SEPARATOR.'directory/file.php');
        $this->assertEquals(Hyde::path('/directory/file.php/'), Hyde::path().DIRECTORY_SEPARATOR.'directory/file.php');
        $this->assertEquals(Hyde::path('\\/directory/file.php/'), Hyde::path().DIRECTORY_SEPARATOR.'directory/file.php');
    }
}
