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
        $this->makeSchemaFile();

        $this->artisan('make:publication')
            ->expectsChoice('Which publication type would you like to create a publication item for?', 0, ['test-publication'])
            ->expectsQuestion('Title', 'Hello World')
            ->expectsOutputToContain('Creating a new Publication!')
            ->expectsOutput('Saving publication data to [test-publication/hello-world.md]')
            ->expectsOutput('Publication created successfully!')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(Hyde::path('test-publication/hello-world.md')));
        $this->assertEqualsIgnoringLineEndingType('---
__createdAt: '.Carbon::now()->format('Y-m-d H:i:s').'
title: Hello World
---
Raw MD text ...
', file_get_contents(Hyde::path('test-publication/hello-world.md')));

        deleteDirectory(Hyde::path('test-publication'));
    }

    protected function makeSchemaFile(): void
    {
        file_put_contents(
            Hyde::path('test-publication/schema.json'),
            json_encode([
                'name'           => 'Test Publication',
                'canonicalField' => 'title',
                'sortField'      => '__createdAt',
                'sortDirection'  => 'ASC',
                'pageSize'       => 10,
                'prevNextLinks'  => true,
                'detailTemplate' => 'test-publication_detail',
                'listTemplate'   => 'test-publication_list',
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
}
