<?php

namespace Hyde\Framework\Testing\Feature\Commands;

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
}
