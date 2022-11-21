<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use function deleteDirectory;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\CommandsMakePublicationCommand
 */
class MakePublicationCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        mkdir(Hyde::path('test-publication'));
    }

    protected function tearDown(): void
    {
        deleteDirectory(Hyde::path('test-publication'));
        parent::tearDown();
    }

    public function test_command_creates_publication()
    {
        file_put_contents(Hyde::path('test-publication/schema.json'),
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
                        "name": "My Title",
                        "min": "0",
                        "max": "0",
                        "type": "string"
                    }
                ]
            }
            JSON
        );

        $this->artisan('make:publication')
            ->expectsQuestion('Publication type (1-1)', 1)
            ->expectsQuestion('My Title', 'Title')
            ->expectsOutputToContain('Creating a new Publication!')
            ->expectsOutput('Choose the default field you wish to sort by:')
            ->expectsOutput('Choose the default sort direction:')
            ->expectsOutput('Saving publication data to [test-publication/schema.json]')
            ->expectsOutput('Publication created successfully!')
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
    }
}
