<?php

namespace Tests\Feature\Commands;

use Hyde\Framework\Hyde;
use Tests\TestCase;

/**
 * @covers \Hyde\Framework\Commands\HydeBuildSearchCommand
 */
class HydeBuildSearchCommandTest extends TestCase
{
    public function test_it_creates_the_search_json_file()
    {
        unlinkIfExists(Hyde::path('_site/docs/search.json'));
        $this->artisan('build:search')
            ->expectsOutput('Generating documentation site search index...')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/docs/search.json'));
        unlink(Hyde::path('_site/docs/search.json'));
    }
}
