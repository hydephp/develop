<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use function deleteDirectory;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\MakePublicationTypeCommand
 */
class MakePublicationTypeCommandTest extends TestCase
{
    public function test_command_creates_publication()
    {
        $this->artisan('make:publicationType')
            ->expectsQuestion('Publication type name', 'Test Publication')
            ->expectsQuestion('Field name', 'Title')
            ->expectsQuestion('Field type (1-7)', 1)
            ->expectsQuestion('Min value (for strings, this refers to string length)', 'default')
            ->expectsQuestion('Max value (for strings, this refers to string length)', 'default')
            ->expectsQuestion('Add another field (y/n)', 'n')
            ->expectsQuestion('Sort field (0-1)', 0)
            ->expectsQuestion('Sort field (1-2)', 1)
            ->expectsQuestion('Enter the pageSize (0 for no limit)', 10)
            ->expectsQuestion('Generate previous/next links in detail view (y/n)', 'n')
            ->expectsQuestion('Canonical field (1-1)', 1)
            ->expectsOutputToContain('Creating a new Publication Type!')
            ->expectsOutput('Choose the default field you wish to sort by:')
            ->expectsOutput('Choose the default sort direction:')
            // ->expectsOutput('Publication type created successfully!')
            ->expectsOutput('Saving publication data to [test-publication/schema.json]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('test-publication/schema.json'));
        $this->assertEqualsIgnoringLineEndingType(
            file_get_contents(Hyde::path('test-publication/schema.json')),
            <<<'JSON'
            {
                "name": "Test Publication",
                "canonicalField": "Title",
                "sortField": "__createdAt",
                "sortDirection": "ASC",
                "pageSize": 10,
                "prevNextLinks": true,
                "detailTemplate": "test-publication_detail",
                "listTemplate": "test-publication_list",
                "fields": [
                    {
                        "name": "Title",
                        "min": "default",
                        "max": "default",
                        "type": "string"
                    }
                ]
            }
            JSON
        );

        // TODO: Assert Blade templates were created?

        deleteDirectory(Hyde::path('test-publication'));
    }
}
