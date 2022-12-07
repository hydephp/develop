<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

use function config;

/**
 * @covers \Hyde\Console\Commands\SeedPublicationCommand
 * @covers \Hyde\Framework\Actions\SeedsPublicationFiles
 */
class SeedPublicationCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->directory('test-publication');
        $this->setupTestPublication();
        $this->pubType = PublicationType::get('test-publication');

        config(['app.throw_on_console_exception' => true]);
    }

    public function test_can_seed_publications()
    {
        $this->artisan('seed:publications')
            ->expectsOutputToContain('Seeding new publications!')
            ->expectsQuestion('Which publication type would you like to seed?', 'test-publication')
            ->expectsQuestion('How many publications would you like to generate', 1)
            ->expectsOutputToContain('1 publications for Test Publication created!')
            ->assertExitCode(0);

        $files = glob(Hyde::path('test-publication/*.md'));
        $this->assertCount(1, $files);
        unlink($files[0]);
    }
}
