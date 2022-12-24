<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use function config;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\MakePublicationTypeCommand
 * @covers \Hyde\Framework\Actions\CreatesNewPublicationType
 */
class MakePublicationTypeCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.throw_on_console_exception' => true]);
    }

    protected function tearDown(): void
    {
        Filesystem::deleteDirectory('test-publication');

        parent::tearDown();
    }

    public function test_command_creates_publication_type()
    {
        $this->artisan('make:publicationType')
            ->expectsQuestion('Publication type name', 'Test Publication')
            ->expectsQuestion('Enter name for field #1', 'Publication Title')
            ->expectsChoice('Enter type for field #1', 'String', [
                'String',
                'Datetime',
                'Boolean',
                'Integer',
                'Float',
                'Image',
                'Array',
                'Text',
                'Url',
                'Tag',
            ], true)
            ->expectsConfirmation('Field #1 added! Add another field?')
            ->expectsConfirmation('Do you want to configure pagination settings?', 'yes')
            ->expectsChoice('Choose the default field you wish to sort by', '__createdAt', [
                '__createdAt',
                'publication-title',
            ])
            ->expectsChoice('Choose the default sort direction', 'Ascending', [
                'Ascending',
                'Descending',
            ])
            ->expectsQuestion('Enter the page size (0 for no limit)', 10)
            ->expectsConfirmation('Generate previous/next links in detail view?', 'yes')
            ->expectsChoice('Choose a canonical name field (this will be used to generate filenames, so the values need to be unique)', 'publication-title', [
                '__createdAt',
                'publication-title',
            ])
            ->expectsOutputToContain('Creating a new Publication Type!')
            ->expectsOutput('Saving publication data to [test-publication/schema.json]')
            ->expectsOutput('Publication type created successfully!')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('test-publication/schema.json'));
        $this->assertEquals(
            <<<'JSON'
            {
                "name": "Test Publication",
                "canonicalField": "publication-title",
                "detailTemplate": "test-publication_detail",
                "listTemplate": "test-publication_list",
                "pagination": {
                    "sortField": "__createdAt",
                    "sortAscending": true,
                    "prevNextLinks": true,
                    "pageSize": 10
                },
                "fields": [
                    {
                        "type": "datetime",
                        "name": "__createdAt"
                    },
                    {
                        "type": "string",
                        "name": "publication-title"
                    }
                ]
            }
            JSON,
            file_get_contents(Hyde::path('test-publication/schema.json'))
        );

        // TODO: Assert Blade templates were created?
    }

    public function test_with_default_values()
    {
        $this->artisan('make:publicationType --use-defaults')
            ->expectsQuestion('Publication type name', 'Test Publication')
            ->expectsQuestion('Enter name for field #1', 'foo')
            ->expectsChoice('Enter type for field #1', 'String', PublicationFieldTypes::names())
            ->expectsOutput('Saving publication data to [test-publication/schema.json]')
            ->expectsOutput('Publication type created successfully!')
            ->assertExitCode(0);
    }

    public function test_with_multiple_fields_of_the_same_name()
    {
        $this->artisan('make:publicationType "Test Publication"')
            ->expectsQuestion('Enter name for field #1', 'foo')
            ->expectsChoice('Enter type for field #1', 'String', PublicationFieldTypes::names())

            ->expectsConfirmation('Field #1 added! Add another field?', 'yes')

            ->expectsQuestion('Enter name for field #2', 'foo')
            ->expectsOutput('Field name [foo] already exists!')
            ->expectsQuestion('Try again: Enter name for field #2', 'bar')
            ->expectsChoice('Enter type for field #2', 'String', PublicationFieldTypes::names())

            ->expectsConfirmation('Field #2 added! Add another field?')

            ->expectsConfirmation('Do you want to configure pagination settings?')
            ->expectsChoice('Choose a canonical name field (this will be used to generate filenames, so the values need to be unique)', 'foo', [
                '__createdAt',
                'bar',
                'foo',
            ])
            ->assertExitCode(0);
    }

    public function test_with_existing_file_of_the_same_name()
    {
        config(['app.throw_on_console_exception' => false]);

        $this->file('test-publication');

        $this->artisan('make:publicationType "Test Publication"')
            ->expectsOutput('Error: Storage path [test-publication] already exists')
            ->assertExitCode(1);
    }

    public function test_with_existing_publication_of_the_same_name()
    {
        config(['app.throw_on_console_exception' => false]);

        $this->directory('test-publication');
        $this->file('test-publication/foo');

        $this->artisan('make:publicationType "Test Publication"')
             ->expectsOutput('Error: Storage path [test-publication] already exists')
             ->assertExitCode(1);
    }
}
