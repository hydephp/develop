<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\HydeKernel;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

class HydeVendorPathHelperTest extends TestCase
{
    public function testMethodExists()
    {
        $this->assertTrue(method_exists(HydeKernel::class, 'vendorPath'));
    }

    public function testMethodReturnsString()
    {
        $this->assertIsString(Hyde::vendorPath());
    }

    public function testMethodReturnsStringContainingVendorPath()
    {
        $this->assertStringContainsString('vendor', Hyde::vendorPath());
    }

    public function testMethodReturnsPathToTheVendorDirectory()
    {
        $this->assertDirectoryExists(Hyde::vendorPath());
        $this->assertFileExists(Hyde::vendorPath().'/composer.json');
        $this->assertStringContainsString('"name": "hyde/framework",', file_get_contents(Hyde::vendorPath().'/composer.json'));
    }

    public function testCanSpecifyWhichHydePackageToUse()
    {
        $this->assertDirectoryExists(Hyde::vendorPath(package: 'realtime-compiler'));
        $this->assertFileExists(Hyde::vendorPath('composer.json', 'realtime-compiler'));
        $this->assertStringContainsString('"name": "hyde/realtime-compiler",', file_get_contents(Hyde::vendorPath('composer.json', 'realtime-compiler')));
    }
}
