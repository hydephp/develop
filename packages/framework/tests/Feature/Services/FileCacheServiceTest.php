<?php

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Hyde;
use Hyde\Framework\Services\FileCacheService;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\FileCacheService
 */
class FileCacheServiceTest extends TestCase
{
    public function test_get_filecache()
    {
        $fileCacheService = new FileCacheService();
        $fileCache = $fileCacheService->getFilecache();

        $this->assertIsArray($fileCache);
        $this->assertArrayHasKey('/resources/views/layouts/app.blade.php', $fileCache);
        $this->assertArrayHasKey('unixsum', $fileCache['/resources/views/layouts/app.blade.php']);
        $this->assertEquals(32, strlen($fileCache['/resources/views/layouts/app.blade.php']['unixsum']));
    }

    public function test_get_checksums()
    {
        $fileCacheService = new FileCacheService();
        $checksums = $fileCacheService->getChecksums();

        $this->assertIsArray($checksums);
        $this->assertEquals(32, strlen($checksums[0]));
    }

    public function test_checksum_matches_any()
    {
        $fileCacheService = new FileCacheService();

        $this->assertTrue($fileCacheService->checksumMatchesAny(FileCacheService::unixsumFile(
            Hyde::vendorPath('resources/views/layouts/app.blade.php'))
        ));
    }

    public function test_checksum_matches_any_false()
    {
        $fileCacheService = new FileCacheService();

        $this->assertFalse($fileCacheService->checksumMatchesAny(FileCacheService::unixsum(
            'foo'
        )));
    }
}
