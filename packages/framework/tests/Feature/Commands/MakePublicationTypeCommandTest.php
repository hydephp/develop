<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use function config;
use Hyde\Facades\Filesystem;
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
            ->expectsQuestion('Field name', 'Publication Title')
            ->expectsChoice('Field type', 'String', [
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
            ->expectsConfirmation('Add another field?', 'n')
            ->expectsChoice('Choose the default field you wish to sort by', 'dateCreated (meta field)', [
                'dateCreated (meta field)',
                'publication-title',
            ])
            ->expectsChoice('Choose the default sort direction', 'Ascending (oldest items first if sorting by dateCreated)', [
                'Ascending (oldest items first if sorting by dateCreated)',
                'Descending (newest items first if sorting by dateCreated)',
            ])
            ->expectsQuestion('Enter the pageSize (0 for no limit)', 10)
            ->expectsQuestion('Generate previous/next links in detail view?', 'n')
            ->expectsChoice('Choose a canonical name field (this will be used to generate filenames, so the values need to be unique)', 'publication-title', [
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
            ->expectsQuestion('Field name', 'Title')
            ->expectsChoice('Field type', 'string', PublicationFieldTypes::collect()->pluck('name')->toArray())
            ->expectsOutput('Saving publication data to [test-publication/schema.json]')
            ->expectsOutput('Publication type created successfully!')
            ->assertExitCode(0);
    }

    public function test_with_multiple_fields_of_the_same_name()
    {
        $this->markTestIncomplete('Unable to test this because of a bug in Mockery');
    }
}
