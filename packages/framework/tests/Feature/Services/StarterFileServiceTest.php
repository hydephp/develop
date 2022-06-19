<?php

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Hyde;
use Hyde\Framework\Services\StarterFileService;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated
 * @covers \Hyde\Framework\Services\StarterFileService
 */
class StarterFileServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (StarterFileService::$files as $file) {
            unlinkIfExists(Hyde::path($file));
        }
    }

    public function testServicePublishesStarterFiles()
    {
        StarterFileService::publish();

        foreach (StarterFileService::$files as $file) {
            $this->assertFileExists(Hyde::path($file));
        }
    }
}
