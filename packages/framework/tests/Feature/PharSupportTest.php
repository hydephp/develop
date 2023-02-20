<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use BadMethodCallException;
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

        $this->assertContains(Hyde::vendorPath(), [
            // Monorepo support for symlinked packages directory
            str_replace('/', DIRECTORY_SEPARATOR, Hyde::path('packages/framework')),
            str_replace('/', DIRECTORY_SEPARATOR, Hyde::path('vendor/hyde/framework')),
        ]);
    }

    public function test_vendor_path_can_run_in_phar_with_path_argument()
    {
        PharSupport::mock('running', true);
        PharSupport::mock('hasVendorDirectory', false);

        $this->assertContains(Hyde::vendorPath('file.php'), [
            // Monorepo support for symlinked packages directory
            str_replace('/', DIRECTORY_SEPARATOR, Hyde::path('packages/framework/file.php')),
            str_replace('/', DIRECTORY_SEPARATOR, Hyde::path('vendor/hyde/framework/file.php')),
        ]);
    }

    public function test_vendor_path_can_run_in_phar_with_package_argument_but_throws()
    {
        PharSupport::mock('running', true);
        PharSupport::mock('hasVendorDirectory', false);

        $this->expectException(BadMethodCallException::class);
        Hyde::vendorPath(package: 'realtime-compiler');
    }
}
