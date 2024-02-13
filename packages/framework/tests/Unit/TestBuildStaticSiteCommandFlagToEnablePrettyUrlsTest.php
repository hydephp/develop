<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * Class TestBuildStaticSiteCommandFlagToEnablePrettyUrlsTest.
 */
class TestBuildStaticSiteCommandFlagToEnablePrettyUrlsTest extends TestCase
{
    public function testPrettyUrlsCanBeEnabledWithFlag()
    {
        config(['hyde.pretty_urls' => false]);

        $this->artisan('build --pretty-urls')
            ->expectsOutput('Generating site with pretty URLs')
            ->assertExitCode(0);

        $this->assertTrue(config('hyde.pretty_urls', false));

        File::cleanDirectory(Hyde::path('_site'));
    }

    public function testConfigChangeIsNotPersisted()
    {
        $this->assertFalse(config('hyde.pretty_urls', false));
    }
}
