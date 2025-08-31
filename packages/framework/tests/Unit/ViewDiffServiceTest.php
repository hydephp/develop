<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Hyde\Framework\Services\ViewDiffService;

use function Hyde\unixsum;
use function Hyde\unixsum_file;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Services\ViewDiffService::class)]
class ViewDiffServiceTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    public function testGetFilecache()
    {
        $fileCache = ViewDiffService::getViewFileHashIndex();

        $this->assertIsArray($fileCache);
        $this->assertArrayHasKey('resources/views/layouts/app.blade.php', $fileCache);
        $this->assertArrayHasKey('unixsum', $fileCache['resources/views/layouts/app.blade.php']);
        $this->assertSame(32, strlen($fileCache['resources/views/layouts/app.blade.php']['unixsum']));
    }

    public function testGetChecksums()
    {
        $checksums = ViewDiffService::getChecksums();

        $this->assertIsArray($checksums);
        $this->assertSame(32, strlen($checksums[0]));
    }

    public function testChecksumMatchesAny()
    {
        $this->assertTrue(ViewDiffService::checksumMatchesAny(
            unixsum_file(Hyde::vendorPath('resources/views/layouts/app.blade.php'))
        ));
    }

    public function testChecksumMatchesAnyFalse()
    {
        $this->assertFalse(ViewDiffService::checksumMatchesAny(unixsum('foo')));
    }
}
