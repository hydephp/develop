<?php

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Framework\Commands\HydeBuildSearchCommand;
use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Commands\HydeBuildSearchCommand
 */
class HydeBuildSearchCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        unlinkIfExists(Hyde::path('_site/docs/search.json'));
        unlinkIfExists(Hyde::path('_site/docs/search.html'));
    }

    protected function tearDown(): void
    {
        unlinkIfExists(Hyde::path('_site/docs/search.html'));
        unlinkIfExists(Hyde::path('_site/docs/search.json'));
        HydeBuildSearchCommand::$guesstimationFactor = 52.5;
        parent::tearDown();
    }

    public function test_it_creates_the_search_json_file()
    {
        $this->artisan('build:search')
            ->expectsOutput('Generating documentation site search index...')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/docs/search.json'));
    }

    public function test_it_creates_the_search_page()
    {
        $this->artisan('build:search')
            ->expectsOutput('Generating documentation site search index...')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/docs/search.html'));
    }

    public function test_it_does_not_create_the_search_page_if_disabled()
    {
        config(['docs.create_search_page' => false]);
        $this->artisan('build:search')
            ->expectsOutput('Generating documentation site search index...')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist(Hyde::path('_site/docs/search.html'));
    }

    public function test_it_does_not_display_the_estimation_message_when_it_is_less_than_1_second()
    {
        HydeBuildSearchCommand::$guesstimationFactor = 0;

        $this->artisan('build:search')
            ->expectsOutput('Generating documentation site search index...')
            ->doesntExpectOutputToContain('> This will take an estimated')
            ->assertExitCode(0);
    }

    public function test_it_displays_the_estimation_message_when_it_is_greater_than_1_second()
    {
        HydeBuildSearchCommand::$guesstimationFactor = 1000;
        touch(Hyde::path('_docs/foo.md'));
        $this->artisan('build:search')
            ->expectsOutput('Generating documentation site search index...')
            ->expectsOutputToContain('> This will take an estimated')
            ->assertExitCode(0);
        unlink(Hyde::path('_docs/foo.md'));
    }
}
