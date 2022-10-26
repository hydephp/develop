<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * Class TestBuildStaticSiteCommandFlagToEnablePrettyUrlsTest.
 */
class TestBuildStaticSiteCommandFlagToEnablePrettyUrlsTest extends TestCase
{
    public function test_pretty_urls_can_be_enabled_with_flag()
    {
        config(['site.pretty_urls' => false]);

        $this->artisan('build --pretty-urls')
            ->expectsOutput('Generating site with pretty URLs')
            ->assertExitCode(0);

        $this->assertTrue(config('site.pretty_urls', false));

        File::cleanDirectory(Hyde::path('_site'));
    }

    public function test_config_change_is_not_persisted()
    {
        $this->assertFalse(config('site.pretty_urls', false));
    }
}
