<?php

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Hyde;
use Hyde\Framework\Services\ViewDiffService;
use Hyde\Framework\Services\ViewDiffService as Service;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\ViewDiffService
 */
class ViewDiffServiceTest extends TestCase
{
    public function test_get_filecache()
    {
        $fileCacheService = new ViewDiffService();
        $fileCache = $fileCacheService->getFilecache();

        $this->assertIsArray($fileCache);
        $this->assertArrayHasKey('/resources/views/layouts/app.blade.php', $fileCache);
        $this->assertArrayHasKey('unixsum', $fileCache['/resources/views/layouts/app.blade.php']);
        $this->assertEquals(32, strlen($fileCache['/resources/views/layouts/app.blade.php']['unixsum']));
    }

    public function test_get_checksums()
    {
        $fileCacheService = new ViewDiffService();
        $checksums = $fileCacheService->getChecksums();

        $this->assertIsArray($checksums);
        $this->assertEquals(32, strlen($checksums[0]));
    }

    public function test_checksum_matches_any()
    {
        $fileCacheService = new ViewDiffService();

        $this->assertTrue($fileCacheService->checksumMatchesAny(ViewDiffService::unixsumFile(
            Hyde::vendorPath('resources/views/layouts/app.blade.php'))
        ));
    }

    public function test_checksum_matches_any_false()
    {
        $fileCacheService = new ViewDiffService();

        $this->assertFalse($fileCacheService->checksumMatchesAny(ViewDiffService::unixsum(
            'foo'
        )));
    }

    public function test_method_returns_string()
    {
        $this->assertIsString(Service::unixsum('foo'));
    }

    public function test_method_returns_string_with_length_of_32()
    {
        $this->assertEquals(32, strlen(Service::unixsum('foo')));
    }

    public function test_method_returns_string_matching_expected_format()
    {
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', Service::unixsum('foo'));
    }

    public function test_method_returns_same_value_for_same_string_using_normal_method()
    {
        $this->assertEquals(md5('foo'), Service::unixsum('foo'));
    }

    public function test_method_returns_different_value_for_different_string()
    {
        $this->assertNotEquals(Service::unixsum('foo'), Service::unixsum('bar'));
    }

    public function test_function_is_case_sensitive()
    {
        $this->assertNotEquals(Service::unixsum('foo'), Service::unixsum('FOO'));
    }

    public function test_function_is_space_sensitive()
    {
        $this->assertNotEquals(Service::unixsum(' foo '), Service::unixsum('foo'));
    }

    public function test_method_returns_same_value_regardless_of_end_of_line_sequence()
    {
        $this->assertEquals(Service::unixsum('foo'), Service::unixsum('foo'));
        $this->assertEquals(Service::unixsum("foo\n"), Service::unixsum("foo\n"));
        $this->assertEquals(Service::unixsum("foo\n"), Service::unixsum("foo\r"));
        $this->assertEquals(Service::unixsum("foo\n"), Service::unixsum("foo\r\n"));
    }

    public function test_method_returns_same_value_for_string_with_mixed_end_of_line_sequences()
    {
        $this->assertEquals(Service::unixsum("foo\nbar\r\nbaz\r\n"),
            Service::unixsum("foo\nbar\nbaz\n"));
    }

    public function test_method_returns_same_value_when_loaded_from_file()
    {
        $string = "foo\nbar\r\nbaz\r\n";
        $file = tempnam(sys_get_temp_dir(), 'foo');
        file_put_contents($file, $string);

        $this->assertEquals(Service::unixsum($string), Service::unixsum(file_get_contents($file)));

        unlink($file);
    }

    public function test_method_returns_same_value_when_loaded_from_file_using_shorthand()
    {
        $string = "foo\nbar\r\nbaz\r\n";
        $file = tempnam(sys_get_temp_dir(), 'foo');
        file_put_contents($file, $string);

        $this->assertEquals(Service::unixsum($string), Service::unixsumFile($file));

        unlink($file);
    }
}
