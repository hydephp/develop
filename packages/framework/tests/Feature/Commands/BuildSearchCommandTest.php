<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Facades\Site;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\BuildSearchCommand
 * @covers \Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateSearch
 */
class BuildSearchCommandTest extends TestCase
{
    public function test_it_creates_the_search_json_file()
    {
        $this->assertFileDoesNotExist(Hyde::path('_site/docs/search.json'));

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('_site/docs/search.json'));

        Filesystem::unlink('_site/docs/search.json');
        Filesystem::unlink('_site/docs/search.html');
    }

    public function test_it_creates_the_search_page()
    {
        $this->assertFileDoesNotExist(Hyde::path('_site/docs/search.html'));

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('_site/docs/search.html'));

        Filesystem::unlink('_site/docs/search.json');
        Filesystem::unlink('_site/docs/search.html');
    }

    public function test_it_does_not_create_the_search_page_if_disabled()
    {
        config(['docs.create_search_page' => false]);

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileDoesNotExist(Hyde::path('_site/docs/search.html'));

        Filesystem::unlink('_site/docs/search.json');
    }

    public function test_it_does_not_display_the_estimation_message_when_it_is_less_than_1_second()
    {
        $this->artisan('build:search')
            ->doesntExpectOutputToContain('> This will take an estimated')
            ->assertExitCode(0);

        Filesystem::unlink('_site/docs/search.json');
        Filesystem::unlink('_site/docs/search.html');
    }

    public function test_it_displays_the_estimation_message_when_it_is_greater_than_or_equal_to_1_second()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->file("_docs/$i.md");
        }

        $this->artisan('build:search')
            ->expectsOutput('This will take an estimated 1.05 seconds. Terminal may seem non-responsive.')
            ->assertExitCode(0);

        Filesystem::unlink('_site/docs/search.json');
        Filesystem::unlink('_site/docs/search.html');
    }

    public function test_search_files_can_be_generated_for_custom_docs_output_directory()
    {
        DocumentationPage::$outputDirectory = 'foo';

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('_site/foo/search.json'));
        $this->assertFileExists(Hyde::path('_site/foo/search.html'));

        Filesystem::deleteDirectory('_site/foo');
    }

    public function test_search_files_can_be_generated_for_custom_site_output_directory()
    {
        Site::$outputPath = 'foo';

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('foo/docs/search.json'));
        $this->assertFileExists(Hyde::path('foo/docs/search.html'));

        Filesystem::deleteDirectory('foo');
    }

    public function test_search_files_can_be_generated_for_custom_site_and_docs_output_directories()
    {
        Site::$outputPath = 'foo';
        DocumentationPage::$outputDirectory = 'bar';

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('foo/bar/search.json'));
        $this->assertFileExists(Hyde::path('foo/bar/search.html'));

        Filesystem::deleteDirectory('foo');
    }

    public function test_search_files_can_be_generated_for_custom_site_and_nested_docs_output_directories()
    {
        Site::$outputPath = 'foo/bar';
        DocumentationPage::$outputDirectory = 'baz';

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('foo/bar/baz/search.json'));
        $this->assertFileExists(Hyde::path('foo/bar/baz/search.html'));

        Filesystem::deleteDirectory('foo');
    }
}
