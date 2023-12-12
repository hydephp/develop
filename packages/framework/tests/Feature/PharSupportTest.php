<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\PharSupport;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\PharSupport
 */
class PharSupportTest extends TestCase
{
    public function tearDown(): void
    {
        PharSupport::clearMocks();

        parent::tearDown();
    }

    public function testActive()
    {
        $this->assertFalse(PharSupport::running());
    }

    public function testMockActive()
    {
        PharSupport::mock('running', true);
        $this->assertTrue(PharSupport::running());

        PharSupport::mock('running', false);
        $this->assertFalse(PharSupport::running());
    }

    public function testHasVendorDirectory()
    {
        $this->assertTrue(PharSupport::hasVendorDirectory());
    }

    public function testMockHasVendorDirectory()
    {
        PharSupport::mock('hasVendorDirectory', true);
        $this->assertTrue(PharSupport::hasVendorDirectory());

        PharSupport::mock('hasVendorDirectory', false);
        $this->assertFalse(PharSupport::hasVendorDirectory());
    }

    public function test_vendor_path_can_run_in_phar()
    {
        PharSupport::mock('running', true);
        PharSupport::mock('hasVendorDirectory', false);

        $this->assertEquals($this->replaceSlashes(Hyde::path("{$this->getBaseVendorPath()}/framework")), Hyde::vendorPath());
    }

    public function test_vendor_path_can_run_in_phar_with_path_argument()
    {
        PharSupport::mock('running', true);
        PharSupport::mock('hasVendorDirectory', false);

        $this->assertEquals($this->replaceSlashes(Hyde::path("{$this->getBaseVendorPath()}/framework/file.php")), Hyde::vendorPath('file.php'));
    }

    protected function getBaseVendorPath(): string
    {
        // Monorepo support for symlinked packages directory
        return str_contains(realpath(Hyde::vendorPath() ?? ''), 'vendor') ? 'vendor/hyde' : 'packages';
    }

    protected function replaceSlashes(string $path): string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
