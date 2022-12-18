<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use function array_merge;
use function config;
use function file_get_contents;
use Hyde\Console\Commands\Helpers\InputStreamHandler;
use Hyde\Facades\Filesystem;
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
        config(['app.throw_on_console_exception' => true]);

        Filesystem::makeDirectory('test-publication');
        Carbon::setTestNow(Carbon::create(2022));
    }

    protected function tearDown(): void
    {
        Filesystem::deleteDirectory('test-publication');
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
        config(['app.throw_on_console_exception' => false]);
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
        config(['app.throw_on_console_exception' => false]);
        $this->makeSchemaFile();

        $this->artisan('make:publication foo')
            ->expectsOutput('Error: Unable to locate publication type [foo]')
            ->assertExitCode(1);
    }

    // text
    public function test_command_with_text_input()
    {
        InputStreamHandler::mockInput("Hello\nWorld");
        $this->makeSchemaFile([
            'canonicalField' => 'description',
            'fields'         =>  [[
                'type' => 'text',
                'name' => 'description',
                'min'  => '0',
                'max'  => '0',
            ],
            ],
        ]);
        $this->artisan('make:publication test-publication')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(Hyde::path('test-publication/hello-world.md')));
        $this->assertStringContainsString("Hello\nWorld", file_get_contents(Hyde::path('test-publication/hello-world.md')));
    }

    // array
    public function test_command_with_array_input()
    {
        InputStreamHandler::mockInput("Foo\nBar");
        $this->makeSchemaFile([
            'fields'         =>  [[
                'type' => 'array',
                'name' => 'tags',
                'min'  => '0',
                'max'  => '0',
            ],
            ],
        ]);

        $this->artisan('make:publication test-publication')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(Hyde::path('test-publication/hello-world.md')));
        $this->assertStringContainsString("Foo\nBar", file_get_contents(Hyde::path('test-publication/hello-world.md')));
    }

    protected function makeSchemaFile(array $merge = []): void
    {
        file_put_contents(
            Hyde::path('test-publication/schema.json'),
            json_encode(array_merge([
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
                'fields'         =>  [
                    [
                        'name' => 'title',
                        'min'  => '0',
                        'max'  => '0',
                        'type' => 'string',
                    ],
                ],
            ], $merge))
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
