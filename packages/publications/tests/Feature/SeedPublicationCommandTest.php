<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Publications\Models\PublicationType;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Publications\Commands\SeedPublicationCommand::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Publications\Actions\SeedsPublicationFiles::class)]
class SeedPublicationCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->directory('test-publication');
        $this->pubType = new PublicationType('Test Publication');
        $this->pubType->save();
    }

    public function testCanSeedPublications()
    {
        $this->artisan('seed:publications')
            ->expectsOutputToContain('Seeding new publications!')
            ->expectsQuestion('Which publication type would you like to seed?', 'test-publication')
            ->expectsQuestion('How many publications would you like to generate', 1)
            ->expectsOutputToContain('1 publication for Test Publication created!')
            ->assertExitCode(0);

        $this->assertPublicationsCreated();
    }

    public function testCanSeedPublicationsUsingArguments()
    {
        $this->artisan('seed:publications test-publication 1')
             ->expectsOutputToContain('Seeding new publications!')
             ->assertExitCode(0);

        $this->assertPublicationsCreated();
    }

    public function testCanSeedMultiplePublications()
    {
        $this->artisan('seed:publications test-publication 2')
             ->expectsOutputToContain('Seeding new publications!')
             ->expectsOutputToContain('2 publications for Test Publication created!')
             ->assertExitCode(0);

        $this->assertPublicationsCreated(2);
    }

    public function testCommandAsksToConfirmBeforeCreatingManyPublications()
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

    public function testCommandAsksToConfirmBeforeCreatingManyPublicationsWhenUsingArguments()
    {
        $this->artisan('seed:publications test-publication 10000')
             ->expectsOutputToContain('Seeding new publications!')
             ->expectsOutputToContain('Warning: Generating a large number of publications may take a while. Expected time: 10 seconds.')
             ->expectsConfirmation('Are you sure you want to continue?', false)
             ->assertExitCode(130);

        $this->assertPublicationsCreated(0);
    }

    public function testWithInvalidPublicationType()
    {
        $this->artisan('seed:publications invalid-publication')
            ->expectsOutput('Error: Unable to locate publication type [invalid-publication]')
            ->assertExitCode(1);
    }

    public function testWithNoPublicationTypes()
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
