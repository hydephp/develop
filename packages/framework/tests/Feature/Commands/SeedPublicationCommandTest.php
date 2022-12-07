<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use function glob;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

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
    }

    public function test_can_seed_publications()
    {
        $this->artisan('seed:publications')
            ->expectsOutputToContain('Seeding new publications!')
            ->expectsQuestion('Which publication type would you like to seed?', 'test-publication')
            ->expectsQuestion('How many publications would you like to generate', 1)
            ->expectsOutputToContain('1 publications for Test Publication created!')
            ->assertExitCode(0);

        $this->assertPublicationsCreated();
    }

    public function test_can_seed_publications_using_arguments()
    {
        $this->artisan('seed:publications test-publication 1')
             ->expectsOutputToContain('Seeding new publications!')
             ->assertExitCode(0);

        $this->assertPublicationsCreated();
    }

    public function test_command_asks_to_confirm_before_creating_many_publications()
    {
        $this->artisan('seed:publications')
             ->expectsOutputToContain('Seeding new publications!')
             ->expectsQuestion('Which publication type would you like to seed?', 'test-publication')
             ->expectsQuestion('How many publications would you like to generate', 10000)
             ->expectsOutputToContain('Warning: Generating a large number of publications may take a while. Expected time: 10 seconds.')
             ->expectsConfirmation('Are you sure you want to continue?', false)
             ->assertExitCode(130);

        $this->assertPublicationsCreated(0);
    }

    public function test_command_asks_to_confirm_before_creating_many_publications_when_using_arguments()
    {
        $this->artisan('seed:publications test-publication 10000')
             ->expectsOutputToContain('Seeding new publications!')
             ->expectsOutputToContain('Warning: Generating a large number of publications may take a while. Expected time: 10 seconds.')
             ->expectsConfirmation('Are you sure you want to continue?', false)
             ->assertExitCode(130);

        $this->assertPublicationsCreated(0);
    }

    public function test_with_invalid_publication_type()
    {
        $this->artisan('seed:publications invalid-publication')
            ->expectsOutput('Error: Unable to locate publication type [invalid-publication]')
            ->assertExitCode(1);
    }

    public function test_with_no_publication_types()
    {
        unlink(Hyde::path('test-publication/schema.json'));
        $this->artisan('seed:publications')
            ->expectsOutput('Error: Unable to locate any publication types. Did you create any?')
            ->assertExitCode(1);
    }

    protected function assertPublicationsCreated(int $expectedCount = 1): void
    {
        $this->assertCount($expectedCount, glob(Hyde::path('test-publication/*.md')));
    }
}
