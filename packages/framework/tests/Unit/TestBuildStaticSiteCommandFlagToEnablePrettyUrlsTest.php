<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\TestCase;

/**
 * Class TestBuildStaticSiteCommandFlagToEnablePrettyUrlsTest.
 */
class TestBuildStaticSiteCommandFlagToEnablePrettyUrlsTest extends TestCase
{
    public function test_pretty_urls_can_be_enabled_with_flag()
    {
        config(['hyde.pretty_urls' => false]);

        $this->artisan('build --pretty-urls')
            ->expectsOutput('Generating site with pretty URLs')
            ->assertExitCode(0);

        $this->assertTrue(config('hyde.pretty_urls', false));
    }

    public function test_config_change_is_not_persisted()
    {
        $this->assertFalse(config('hyde.pretty_urls', false));
    }
}
