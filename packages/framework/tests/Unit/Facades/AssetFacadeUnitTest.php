<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Facades\Asset;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Facades\Asset
 *
 * @see \Hyde\Framework\Testing\Feature\AssetFacadeTest
 */
class AssetFacadeUnitTest extends UnitTestCase
{
    protected function setUp(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    public function testHasMediaFileHelper()
    {
        $this->assertFalse(Asset::hasMediaFile('styles.css'));
    }

    public function testHasMediaFileHelperReturnsTrueForExistingFile()
    {
        $this->assertTrue(Asset::hasMediaFile('app.css'));
    }
}
