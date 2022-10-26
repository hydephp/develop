<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\HydeKernel;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

class HydeVendorPathHelperTest extends TestCase
{
    public function test_method_exists()
    {
        $this->assertTrue(method_exists(HydeKernel::class, 'vendorPath'));
    }

    public function test_method_returns_string()
    {
        $this->assertIsString(Hyde::vendorPath());
    }

    public function test_method_returns_string_containing_vendor_path()
    {
        $this->assertStringContainsString('vendor', Hyde::vendorPath());
    }

    public function test_method_returns_path_to_the_vendor_directory()
    {
        $this->assertDirectoryExists(Hyde::vendorPath());
        $this->assertFileExists(Hyde::vendorPath().'/composer.json');
        $this->assertStringContainsString('"name": "hyde/framework",', file_get_contents(Hyde::vendorPath().'/composer.json'));
    }
}
