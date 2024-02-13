<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Foundation\PackageManifest;

/**
 * @covers \Hyde\Console\Commands\PackageDiscoverCommand
 */
class PackageDiscoverCommandTest extends TestCase
{
    public function testPackageDiscoverCommandRegistersManifestPath()
    {
        $this->artisan('package:discover')->assertExitCode(0);

        $this->assertEquals(Hyde::path('app/storage/framework/cache/packages.php'),
            $this->app->make(PackageManifest::class)->manifestPath
        );

        $this->assertFileExists(Hyde::path('app/storage/framework/cache/packages.php'));
    }
}
