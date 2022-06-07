<?php

namespace Tests\Feature\Services;

use Hyde\Framework\Hyde;
use Hyde\Framework\Services\FileCacheService;
use Tests\TestCase;

/**
 * @covers \Hyde\Framework\Services\FileCacheService
 */
class FileCacheServiceTest extends TestCase
{
    // Test getFilecache() method returns array containing checksums for vendor files
    public function testGetFilecache()
    {
        $fileCacheService = new FileCacheService();
        $fileCache = $fileCacheService->getFilecache();

        $this->assertIsArray($fileCache);
        $this->assertArrayHasKey('/resources/views/layouts/app.blade.php', $fileCache);
        $this->assertArrayHasKey('unixsum', $fileCache['/resources/views/layouts/app.blade.php']);
        $this->assertEquals(32, strlen($fileCache['/resources/views/layouts/app.blade.php']['unixsum']));
    }

    // Test getChecksums() method returns array with just the checksums
    public function testGetChecksums()
    {
        $fileCacheService = new FileCacheService();
        $checksums = $fileCacheService->getChecksums();

        $this->assertIsArray($checksums);
        $this->assertEquals(32, strlen($checksums[0]));
    }

    // Test checksumMatchesAny() method returns true if a supplied checksum matches any of the checksums in array
    public function testChecksumMatchesAny()
    {
        $fileCacheService = new FileCacheService();

        $this->assertTrue($fileCacheService->checksumMatchesAny(FileCacheService::unixsumFile(
            Hyde::vendorPath('resources/views/layouts/app.blade.php'))
        ));
    }

    // Test checksumMatchesAny() method returns false if a supplied checksum does not match any of the checksums in array
    public function testChecksumMatchesAnyFalse()
    {
        $fileCacheService = new FileCacheService();

        $this->assertFalse($fileCacheService->checksumMatchesAny(FileCacheService::unixsum(
            'foo'
        )));
    }
}
