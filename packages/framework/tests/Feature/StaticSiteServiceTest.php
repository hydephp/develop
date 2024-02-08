<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Support\BuildWarnings;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Console\Commands\BuildSiteCommand
 * @covers \Hyde\Framework\Services\BuildService
 * @covers \Hyde\Framework\Actions\PreBuildTasks\CleanSiteDirectory
 */
class StaticSiteServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetSite();
    }

    protected function tearDown(): void
    {
        File::cleanDirectory(Hyde::path('_site'));

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
        $this->file('_posts/test-post.md');

        $this->artisan('build')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/index.html'));
        $this->assertFileExists(Hyde::path('_site/posts/test-post.html'));
    }

    public function test_build_command_transfers_media_asset_files()
    {
        file_put_contents(Hyde::path('_media/test-image.png'), 'foo');
        $this->artisan('build');
        $this->assertFileEquals(Hyde::path('_media/test-image.png'), Hyde::path('_site/media/test-image.png'));
        Filesystem::unlink('_media/test-image.png');
        Filesystem::unlink('_site/media/test-image.png');
    }

    public function test_build_command_transfers_media_asset_files_recursively()
    {
        $this->directory('_media/foo');

        file_put_contents(Hyde::path('_media/foo/img.png'), 'foo');
        $this->artisan('build')->assertSuccessful();
        $this->assertFileEquals(Hyde::path('_media/foo/img.png'), Hyde::path('_site/media/foo/img.png'));
    }

    public function test_all_page_types_can_be_compiled()
    {
        $this->file('_pages/html.html');
        $this->file('_pages/blade.blade.php');
        $this->file('_pages/markdown.md');
        $this->file('_posts/post.md');
        $this->file('_docs/docs.md');

        $this->artisan('build')
            ->expectsOutput('Creating Html Pages...')
            ->expectsOutput('Creating Blade Pages...')
            ->expectsOutput('Creating Markdown Pages...')
            ->expectsOutput('Creating Markdown Posts...')
            ->expectsOutput('Creating Documentation Pages...')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/html.html'));
        $this->assertFileExists(Hyde::path('_site/blade.html'));
        $this->assertFileExists(Hyde::path('_site/markdown.html'));
        $this->assertFileExists(Hyde::path('_site/posts/post.html'));
        $this->assertFileExists(Hyde::path('_site/docs/docs.html'));

        Filesystem::unlink('_site/html.html');
        Filesystem::unlink('_site/blade.html');
        Filesystem::unlink('_site/markdown.html');
        Filesystem::unlink('_site/posts/post.html');
        Filesystem::unlink('_site/docs/docs.html');
    }

    public function test_only_progress_bars_for_types_with_pages_are_shown()
    {
        $this->file('_pages/blade.blade.php');
        $this->file('_pages/markdown.md');

        $this->artisan('build')
            ->doesntExpectOutput('Creating Html Pages...')
            ->expectsOutput('Creating Blade Pages...')
            ->expectsOutput('Creating Markdown Pages...')
            ->doesntExpectOutput('Creating Markdown Posts...')
            ->doesntExpectOutput('Creating Documentation Pages...')
            ->doesntExpectOutputToContain('Creating')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/blade.html'));
        $this->assertFileExists(Hyde::path('_site/markdown.html'));
        Filesystem::unlink('_site/blade.html');
        Filesystem::unlink('_site/markdown.html');
    }

    public function test_print_initial_information_allows_api_to_be_disabled()
    {
        $this->artisan('build --no-api')
            ->expectsOutput('Disabling external API calls')
            ->assertExitCode(0);
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
        config(['hyde.url' => '']);
        config(['hyde.generate_sitemap' => false]);

        $this->artisan('build')
            ->doesntExpectOutput('Generating sitemap...')
            ->assertExitCode(0);
    }

    public function test_sitemap_is_generated_when_conditions_are_met()
    {
        config(['hyde.url' => 'https://example.com']);
        config(['hyde.generate_sitemap' => true]);

        $this->artisan('build')
            // ->expectsOutput('Generating sitemap...')
            ->assertExitCode(0);
        Filesystem::unlink('_site/sitemap.xml');
    }

    public function test_rss_feed_is_not_generated_when_conditions_are_not_met()
    {
        config(['hyde.url' => '']);
        config(['hyde.rss.enabled' => false]);

        $this->artisan('build')
            ->doesntExpectOutput('Generating RSS feed...')
            ->assertExitCode(0);
    }

    public function test_rss_feed_is_generated_when_conditions_are_met()
    {
        config(['hyde.url' => 'https://example.com']);
        config(['hyde.rss.enabled' => true]);

        Filesystem::touch('_posts/foo.md');

        $this->artisan('build')
            // ->expectsOutput('Generating RSS feed...')
            ->assertExitCode(0);

        Filesystem::unlink('_posts/foo.md');
        Filesystem::unlink('_site/feed.xml');
    }

    public function test_does_not_generate_search_files_when_conditions_are_not_met()
    {
        $this->artisan('build')
            ->doesntExpectOutput('Generating search index...')
            ->doesntExpectOutput('Generating search page...')
            ->assertExitCode(0);
    }

    public function test_generates_search_files_when_conditions_are_met()
    {
        Filesystem::touch('_docs/foo.md');

        $this->artisan('build')
            // ->expectsOutput('Generating search index...')
            // ->expectsOutput('Generating search page...')
            ->assertExitCode(0);

        Filesystem::unlink('_docs/foo.md');
    }

    public function test_site_directory_is_emptied_before_build()
    {
        Filesystem::touch('_site/foo.html');
        $this->artisan('build')
            ->expectsOutputToContain('Removing all files from build directory...')
            ->assertExitCode(0);
        $this->assertFileDoesNotExist(Hyde::path('_site/foo.html'));
    }

    public function test_output_directory_is_not_emptied_if_disabled_in_config()
    {
        config(['hyde.empty_output_directory' => false]);
        Filesystem::touch('_site/keep.html');

        $this->artisan('build')
            ->doesntExpectOutput('Removing all files from build directory...')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/keep.html'));
        Filesystem::unlink('_site/keep.html');
    }

    public function test_aborts_when_non_standard_directory_is_emptied()
    {
        Hyde::setOutputDirectory('foo');

        mkdir(Hyde::path('foo'));
        Filesystem::touch('foo/keep.html');

        $this->artisan('build')
            ->expectsOutputToContain('Removing all files from build directory...')
            ->expectsQuestion('The configured output directory (foo) is potentially unsafe to empty. Are you sure you want to continue?', false)
            ->expectsOutput('Output directory will not be emptied.')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('foo/keep.html'));
        File::deleteDirectory(Hyde::path('foo'));
    }

    public function test_without_warnings()
    {
        $this->artisan('build')
            ->doesntExpectOutput('There were some warnings during the build process:')
            ->assertExitCode(0);
    }

    public function test_with_warnings()
    {
        BuildWarnings::report('This is a warning');

        $this->artisan('build')
            ->expectsOutput('There were some warnings during the build process:')
            ->expectsOutput(' 1. This is a warning')
            ->assertExitCode(0);
    }

    public function test_with_warnings_and_verbose()
    {
        BuildWarnings::report('This is a warning');

        $this->artisan('build --verbose')
            ->expectsOutput('There were some warnings during the build process:')
            ->expectsOutput(' 1. This is a warning')
            ->expectsOutputToContain('BuildWarnings.php')
            ->assertExitCode(0);
    }

    public function test_with_warnings_but_warnings_are_disabled()
    {
        config(['hyde.log_warnings' => false]);
        BuildWarnings::report('This is a warning');

        $this->artisan('build')
            ->doesntExpectOutput('There were some warnings during the build process:')
            ->assertExitCode(0);
    }

    public function test_with_warnings_converted_to_exceptions()
    {
        config(['hyde.convert_build_warnings_to_exceptions' => true]);
        BuildWarnings::report('This is a warning');

        $this->artisan('build')
            ->expectsOutput('There were some warnings during the build process:')
            ->expectsOutputToContain('Hyde\Framework\Exceptions\BuildWarning')
            ->doesntExpectOutput(' 1. This is a warning')
            ->assertExitCode(2);
    }
}
