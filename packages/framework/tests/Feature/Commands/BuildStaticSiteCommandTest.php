<?php

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Commands\HydeBuildStaticSiteCommand
 */
class BuildStaticSiteCommandTest extends TestCase
{
    use ResetsApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetSite();
    }

    protected function tearDown(): void
    {
        $this->resetSite();

        parent::tearDown();
    }

    public function test_build_command_contains_expected_output()
    {
        $this->artisan('build')
            ->expectsOutputToContain('Building your static site')
            ->expectsOutputToContain('All done! Finished in')
            ->expectsOutput('Congratulations! 🎉 Your static site has been built!')
            ->assertExitCode(0);
    }

    public function test_build_command_creates_html_files()
    {
        $post = createTestPost();

        $this->artisan('build')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/index.html'));
        $this->assertFileExists(Hyde::path('_site/posts/test-post.html'));

        unlinkIfExists($post);
    }

    public function test_build_command_transfers_media_asset_files()
    {
        file_put_contents(Hyde::path('_media/test-image.png'), 'foo');
        $this->artisan('build');
        $this->assertFileEquals(Hyde::path('_media/test-image.png'), Hyde::path('_site/media/test-image.png'));
        unlink(Hyde::path('_media/test-image.png'));
        unlink(Hyde::path('_site/media/test-image.png'));
    }

    public function test_print_initial_information_allows_api_to_be_disabled()
    {
        $this->artisan('build --no-api')
            ->expectsOutput('Disabling external API calls')
            ->assertExitCode(0);
    }

    public function test_handle_purge_method()
    {
        touch(Hyde::path('_site/foo.html'));
        $this->artisan('build')
            ->expectsOutput('Removing all files from build directory.')
            ->expectsOutput(' > Directory purged')
            ->expectsOutput(' > Recreating directories')
            ->assertExitCode(0);
        $this->assertFileDoesNotExist(Hyde::path('_site/foo.html'));
    }

    public function test_node_action_outputs()
    {
        $this->artisan('build --run-prettier --run-dev --run-prod')
            ->expectsOutput('Prettifying code! This may take a second.')
            ->expectsOutput('Building frontend assets for development! This may take a second.')
            ->expectsOutput('Building frontend assets for production! This may take a second.')
            ->assertExitCode(0);
    }

    public function test_pretty_urls_option_output()
    {
        $this->artisan('build --pretty-urls')
            ->expectsOutput('Generating site with pretty URLs')
            ->assertExitCode(0);
    }

    public function test_sitemap_is_not_generated_when_conditions_are_not_met()
    {
        config(['hyde.site_url' => '']);
        config(['hyde.generate_sitemap' => false]);

        $this->artisan('build')
            ->doesntExpectOutput('Generating sitemap...')
            ->assertExitCode(0);
    }

    public function test_sitemap_is_generated_when_conditions_are_met()
    {
        config(['hyde.site_url' => 'https://example.com']);
        config(['hyde.generate_sitemap' => true]);

        $this->artisan('build')
            ->expectsOutput('Generating sitemap...')
            ->assertExitCode(0);
        unlink(Hyde::path('_site/sitemap.xml'));
    }

    public function test_rss_feed_is_not_generated_when_conditions_are_not_met()
    {
        config(['hyde.site_url' => '']);
        config(['hyde.generate_rss_feed' => false]);

        $this->artisan('build')
            ->doesntExpectOutput('Generating RSS feed...')
            ->assertExitCode(0);
    }

    public function test_rss_feed_is_generated_when_conditions_are_met()
    {
        config(['hyde.site_url' => 'https://example.com']);
        config(['hyde.generate_rss_feed' => true]);

        touch(Hyde::path('_posts/foo.md'));

        $this->artisan('build')
            ->expectsOutput('Generating RSS feed...')
            ->assertExitCode(0);

        unlink(Hyde::path('_site/feed.xml'));
    }

    public function test_does_not_generate_search_files_when_conditions_are_not_met()
    {
        $this->artisan('build')
            ->doesntExpectOutput('Generating documentation site search index...')
            ->doesntExpectOutput('Generating search page...')
            ->assertExitCode(0);
    }

    public function test_generates_search_files_when_conditions_are_met()
    {
        touch(Hyde::path('_docs/foo.md'));

        $this->artisan('build')
            ->expectsOutput('Generating documentation site search index...')
            ->expectsOutput('Generating search page...')
            ->assertExitCode(0);
    }

    /**
     * Added for code coverage, deprecated as the pretty flag is deprecated.
     *
     * @deprecated
     */
    public function test_command_warns_when_deprecated_pretty_flag_is_used()
    {
        $this->artisan('build --pretty')
            ->expectsOutput('Warning: The --pretty option is deprecated, use --run-prettier instead')
            ->assertExitCode(0);
    }
}
