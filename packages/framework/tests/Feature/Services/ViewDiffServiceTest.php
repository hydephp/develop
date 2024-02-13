<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Services\ViewDiffService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

use function Hyde\unixsum;
use function Hyde\unixsum_file;

/**
 * @covers \Hyde\Framework\Services\ViewDiffService
 */
class ViewDiffServiceTest extends TestCase
{
    public function testGetFilecache()
    {
        $fileCacheService = new ViewDiffService();
        $fileCache = $fileCacheService->getViewFileHashIndex();

        $this->assertIsArray($fileCache);
        $this->assertArrayHasKey('resources/views/layouts/app.blade.php', $fileCache);
        $this->assertArrayHasKey('unixsum', $fileCache['resources/views/layouts/app.blade.php']);
        $this->assertEquals(32, strlen($fileCache['resources/views/layouts/app.blade.php']['unixsum']));
    }

    public function testGetChecksums()
    {
        $fileCacheService = new ViewDiffService();
        $checksums = $fileCacheService->getChecksums();

        $this->assertIsArray($checksums);
        $this->assertEquals(32, strlen($checksums[0]));
    }

    public function testChecksumMatchesAny()
    {
        $fileCacheService = new ViewDiffService();

        $this->assertTrue($fileCacheService->checksumMatchesAny(
            unixsum_file(Hyde::vendorPath('resources/views/layouts/app.blade.php'))
        ));
    }

    public function testChecksumMatchesAnyFalse()
    {
        $fileCacheService = new ViewDiffService();

        $this->assertFalse($fileCacheService->checksumMatchesAny(unixsum('foo')));
    }
}
