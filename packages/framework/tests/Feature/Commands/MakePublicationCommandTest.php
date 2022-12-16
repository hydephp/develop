<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use function deleteDirectory;
use function file_get_contents;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Console\Commands\MakePublicationCommand
 * @covers \Hyde\Framework\Actions\CreatesNewPublicationPage
 */
class MakePublicationCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        mkdir(Hyde::path('test-publication'));

        Carbon::setTestNow(Carbon::create(2022));
    }

    protected function tearDown(): void
    {
        deleteDirectory(Hyde::path('test-publication'));
        parent::tearDown();
    }

    public function test_command_creates_publication()
    {
        $this->makeSchemaFile();

        $this->artisan('make:publication')
            ->expectsOutputToContain('Creating a new Publication!')
            ->expectsChoice('Which publication type would you like to create a publication item for?', 0, ['test-publication'])
            ->expectsOutput('Creating a new publication of type [test-publication]')
            ->expectsQuestion('Title', 'Hello World')
            ->expectsOutput('Saving publication data to [test-publication/hello-world.md]')
            ->expectsOutput('Publication created successfully!')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(Hyde::path('test-publication/hello-world.md')));
        $this->assertPublicationFileWasCreatedCorrectly();
    }

    public function test_command_with_no_publication_types()
    {
        $this->artisan('make:publication')
            ->expectsOutputToContain('Creating a new Publication!')
            ->expectsOutput('Error: Unable to locate any publication types. Did you create any?')
            ->assertExitCode(1);
    }

    public function test_command_with_existing_publication()
    {
        $this->makeSchemaFile();
        file_put_contents(Hyde::path('test-publication/hello-world.md'), 'foo');

        $this->artisan('make:publication')
            ->expectsOutputToContain('Creating a new Publication!')
            ->expectsChoice('Which publication type would you like to create a publication item for?', 0, ['test-publication'])
            ->expectsQuestion('Title', 'Hello World')
            ->expectsOutput('Error: A publication already exists with the same canonical field value')
            ->expectsConfirmation('Do you wish to overwrite the existing file?')
            ->expectsOutput('Exiting without overwriting existing publication file!')
            ->doesntExpectOutput('Publication created successfully!')
            ->assertExitCode(130);

        $this->assertSame('foo', file_get_contents(Hyde::path('test-publication/hello-world.md')));
    }

    public function test_command_with_existing_publication_and_overwrite()
    {
        $this->makeSchemaFile();
        file_put_contents(Hyde::path('test-publication/hello-world.md'), 'foo');

        $this->artisan('make:publication')
             ->expectsOutputToContain('Creating a new Publication!')
             ->expectsChoice('Which publication type would you like to create a publication item for?', 0, ['test-publication'])
             ->expectsQuestion('Title', 'Hello World')
             ->expectsOutput('Error: A publication already exists with the same canonical field value')
             ->expectsConfirmation('Do you wish to overwrite the existing file?', 'yes')
             ->expectsOutput('Publication created successfully!')
             ->assertExitCode(0);

        $this->assertNotEquals('foo', file_get_contents(Hyde::path('test-publication/hello-world.md')));
    }

    public function test_can_overwrite_existing_publication_by_passing_force_flag()
    {
        $this->makeSchemaFile();
        file_put_contents(Hyde::path('test-publication/hello-world.md'), 'foo');

        $this->artisan('make:publication', ['--force' => true])
             ->expectsOutputToContain('Creating a new Publication!')
             ->expectsChoice('Which publication type would you like to create a publication item for?', 0, ['test-publication'])
             ->expectsQuestion('Title', 'Hello World')
             ->expectsOutput('Publication created successfully!')
             ->assertExitCode(0);

        $this->assertNotEquals('foo', file_get_contents(Hyde::path('test-publication/hello-world.md')));
    }

    public function test_command_with_publication_type_passed_as_argument()
    {
        $this->makeSchemaFile();

        $this->artisan('make:publication test-publication')
            ->expectsOutput('Creating a new publication of type [test-publication]')
            ->expectsQuestion('Title', 'Hello World')
            ->expectsOutput('Saving publication data to [test-publication/hello-world.md]')
            ->expectsOutput('Publication created successfully!')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(Hyde::path('test-publication/hello-world.md')));
        $this->assertPublicationFileWasCreatedCorrectly();
    }

    public function test_command_with_invalid_publication_type_passed_as_argument()
    {
        $this->makeSchemaFile();

        $this->artisan('make:publication foo')
            ->expectsOutput('Error: Unable to locate publication type [foo]')
            ->assertExitCode(1);
    }

    protected function makeSchemaFile(): void
    {
        file_put_contents(
            Hyde::path('test-publication/schema.json'),
            json_encode([
                'name'           => 'Test Publication',
                'canonicalField' => 'title',
                'detailTemplate' => 'test-publication_detail',
                'listTemplate'   => 'test-publication_list',
                'pagination' => [
                    'pageSize'       => 10,
                    'prevNextLinks'  => true,
                    'sortField'      => '__createdAt',
                    'sortAscending'  => true,
                ],
                'fields'         => [
                    [
                        'name' => 'title',
                        'min'  => '0',
                        'max'  => '0',
                        'type' => 'string',
                    ],
                ],
            ])
        );
    }

    protected function assertPublicationFileWasCreatedCorrectly(): void
    {
        $this->assertEquals(
            <<<'MARKDOWN'
            ---
            __createdAt: 2022-01-01 00:00:00
            title: Hello World
            ---
            
            ## Write something awesome.
            
            
            MARKDOWN, file_get_contents(Hyde::path('test-publication/hello-world.md'))
        );
    }
}
