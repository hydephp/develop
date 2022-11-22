<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use function deleteDirectory;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use function file_get_contents;
use function rmdir;
use function unlink;

/**
 * @covers \Hyde\Console\CommandsMakePublicationCommand
 * @covers \Hyde\Framework\Actions\CreatesNewPublicationFile
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
                        "name": "title",
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
            ->expectsQuestion('Title', 'Hello World')
            ->expectsOutputToContain('Creating a new Publication!')
            ->expectsOutput('Publication created successfully!')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(Hyde::path('test-publication/hello-world.md')));
        $this->assertEqualsIgnoringLineEndingType('---
__createdAt: '.Carbon::now()->format('Y-m-d H:i:s').'
title: Hello World
---
Raw MD text ...
', file_get_contents(Hyde::path('test-publication/hello-world.md')));

        unlink(Hyde::path('test-publication/hello-world.md'));
        rmdir(Hyde::path('test-publication'));
    }
}
