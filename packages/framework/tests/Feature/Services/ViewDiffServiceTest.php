<?php

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Hyde;
use Hyde\Framework\Services\ViewDiffService;
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
}
