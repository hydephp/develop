<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

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

    public function test_command_creates_publication_type()
    {
        $this->artisan('make:publicationType')
            ->expectsQuestion('Publication type name', 'Test Publication')
            ->expectsQuestion('Field name', 'Publication Title')
            ->expectsChoice('Field type', 'String', [
                1 => 'String',
                2 => 'Boolean',
                3 => 'Integer',
                4 => 'Float',
                5 => 'Datetime (YYYY-MM-DD (HH:MM:SS))',
                6 => 'URL',
                7 => 'Array',
                8 => 'Text',
                9 => 'Local Image',
                10 => 'Tag (select value from list)',
            ])
            ->expectsQuestion('<bg=magenta;fg=white>Add another field (y/n)</>', 'n')
            ->expectsChoice('Choose the default field you wish to sort by', 'dateCreated (meta field)', [
                'dateCreated (meta field)',
                'publication-title',
            ])
            ->expectsChoice('Choose the default sort direction', 'Ascending (oldest items first if sorting by dateCreated)', [
                'Ascending (oldest items first if sorting by dateCreated)',
                'Descending (newest items first if sorting by dateCreated)',
            ])
            ->expectsQuestion('Enter the pageSize (0 for no limit)', 10)
            ->expectsQuestion('Generate previous/next links in detail view (y/n)', 'n')
            ->expectsChoice('Choose a canonical name field (the values of this field have to be unique!)', 'publication-title', [
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

        Filesystem::deleteDirectory('test-publication');
    }
}
