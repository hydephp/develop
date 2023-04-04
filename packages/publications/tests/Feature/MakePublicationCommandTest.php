<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use function array_merge;
use function file_get_contents;
use function file_put_contents;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Publications\Commands\Helpers\InputStreamHandler;
use Hyde\Testing\TestCase;
use Illuminate\Support\Carbon;

use function json_encode;

/**
 * @covers \Hyde\Publications\Commands\MakePublicationCommand
 * @covers \Hyde\Publications\Actions\CreatesNewPublicationPage
 */
class MakePublicationCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->throwOnConsoleException();

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
             ->expectsOutputToContain('Creating a new publication!')
             ->expectsChoice('Which publication type would you like to create a publication item for?', 'test-publication', ['test-publication'])
             ->expectsOutput('Creating a new publication of type [test-publication]')
             ->expectsQuestion('Enter data for field </>[<comment>title</comment>]', 'Hello World')
             ->expectsOutput('All done! Created file [test-publication/hello-world.md]')
             ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('test-publication/hello-world.md'));
        $this->assertPublicationFileWasCreatedCorrectly();
    }

    public function test_command_with_no_publication_types()
    {
        $this->throwOnConsoleException(false);
        $this->artisan('make:publication')
             ->expectsOutputToContain('Creating a new publication!')
             ->expectsOutput('Error: Unable to locate any publication types. Did you create any?')
             ->assertExitCode(1);
    }

    public function test_command_selects_the_right_publication_using_the_names()
    {
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [],
        ]);
        $this->directory('second-publication');
        file_put_contents(
            Hyde::path('second-publication/schema.json'),
            json_encode([
                'name'           => 'Second Publication',
                'canonicalField' => '__createdAt',
                'detailTemplate' => 'detail',
                'listTemplate'   => 'list',
                'pageSize'       => 10,
                'sortField'      => '__createdAt',
                'sortAscending'  => true,
                'fields'         =>  [],
            ])
        );

        $this->artisan('make:publication')
            ->expectsOutputToContain('Creating a new publication!')
            ->expectsChoice('Which publication type would you like to create a publication item for?', 'test-publication', [
                'second-publication',
                'test-publication',
            ], true)
            ->expectsOutput('Creating a new publication of type [test-publication]')
            ->expectsOutput('All done! Created file [test-publication/2022-01-01-000000.md]')
            ->assertExitCode(0);

        $this->artisan('make:publication')
            ->expectsOutputToContain('Creating a new publication!')
            ->expectsChoice('Which publication type would you like to create a publication item for?', 'second-publication', [
                'second-publication',
                'test-publication',
            ], true)
            ->expectsOutput('Creating a new publication of type [second-publication]')
            ->expectsOutput('All done! Created file [second-publication/2022-01-01-000000.md]')
            ->assertExitCode(0);
    }

    public function test_command_with_existing_publication()
    {
        $this->makeSchemaFile();
        file_put_contents(Hyde::path('test-publication/hello-world.md'), 'foo');

        $this->artisan('make:publication')
             ->expectsOutputToContain('Creating a new publication!')
             ->expectsChoice('Which publication type would you like to create a publication item for?', 'test-publication', ['test-publication'])
             ->expectsQuestion('Enter data for field </>[<comment>title</comment>]', 'Hello World')
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
             ->expectsOutputToContain('Creating a new publication!')
             ->expectsChoice('Which publication type would you like to create a publication item for?', 'test-publication', ['test-publication'])
             ->expectsQuestion('Enter data for field </>[<comment>title</comment>]', 'Hello World')
             ->expectsOutput('Error: A publication already exists with the same canonical field value')
             ->expectsConfirmation('Do you wish to overwrite the existing file?', 'yes')
             ->assertExitCode(0);

        $this->assertNotEquals('foo', file_get_contents(Hyde::path('test-publication/hello-world.md')));
    }

    public function test_can_overwrite_existing_publication_by_passing_force_flag()
    {
        $this->makeSchemaFile();
        file_put_contents(Hyde::path('test-publication/hello-world.md'), 'foo');

        $this->artisan('make:publication', ['--force' => true])
             ->expectsOutputToContain('Creating a new publication!')
             ->expectsChoice('Which publication type would you like to create a publication item for?', 'test-publication', ['test-publication'])
             ->expectsQuestion('Enter data for field </>[<comment>title</comment>]', 'Hello World')
             ->assertExitCode(0);

        $this->assertNotEquals('foo', file_get_contents(Hyde::path('test-publication/hello-world.md')));
    }

    public function test_command_with_publication_type_passed_as_argument()
    {
        $this->makeSchemaFile();

        $this->artisan('make:publication test-publication')
             ->expectsOutput('Creating a new publication of type [test-publication]')
             ->expectsQuestion('Enter data for field </>[<comment>title</comment>]', 'Hello World')
             ->expectsOutput('All done! Created file [test-publication/hello-world.md]')
             ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('test-publication/hello-world.md'));
        $this->assertPublicationFileWasCreatedCorrectly();
    }

    public function test_command_with_invalid_publication_type_passed_as_argument()
    {
        $this->throwOnConsoleException(false);
        $this->makeSchemaFile();

        $this->artisan('make:publication foo')
             ->expectsOutput('Error: Unable to locate publication type [foo]')
             ->assertExitCode(1);
    }

    public function test_command_with_schema_using_canonical_meta_field()
    {
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields' => [],
        ]);

        $this->artisan('make:publication test-publication')
             ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('test-publication/2022-01-01-000000.md'));
        $this->assertEquals(
            <<<'MARKDOWN'
            ---
            __createdAt: 2022-01-01T00:00:00+00:00
            ---
            
            ## Write something awesome.
            
            
            MARKDOWN, file_get_contents(Hyde::path('test-publication/2022-01-01-000000.md')));
    }

    public function test_command_does_not_ask_user_to_fill_in_meta_fields()
    {
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields' => [[
                'type' => 'string',
                'name' => '__createdAt',
            ]],
        ]);

        $this->artisan('make:publication test-publication')
            ->doesntExpectOutput('Enter data for field </>[<comment>__createdAt</comment>]')
            ->doesntExpectOutputToContain('__createdAt')
            ->assertExitCode(0);

        $this->assertDatedPublicationExists();
    }

    public function test_command_with_text_input()
    {
        InputStreamHandler::mockInput("Hello\nWorld\n<<<");
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'text',
                'name' => 'description',
            ],
            ],
        ]);

        $this->artisan('make:publication test-publication')
             ->assertExitCode(0);

        $this->assertDatedPublicationExists();
        $this->assertCreatedPublicationMatterEquals('description: |
    Hello
    World'
        );
    }

    public function test_command_with_boolean_input()
    {
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'boolean',
                'name' => 'published',
            ],
            ],
        ]);
        $this->artisan('make:publication test-publication')
            ->expectsQuestion('Enter data for field </>[<comment>published</comment>]', 'true')
             ->assertExitCode(0);

        $this->assertDatedPublicationExists();
        $this->assertCreatedPublicationMatterEquals('published: true');
    }

    public function test_command_with_array_input()
    {
        InputStreamHandler::mockInput("First Tag\nSecond Tag\nThird Tag\n<<<");
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'array',
                'name' => 'tags',
            ],
            ],
        ]);

        $this->artisan('make:publication test-publication')
             ->assertExitCode(0);

        $this->assertDatedPublicationExists();
        $this->assertCreatedPublicationMatterEquals(
            "tags:
    - 'First Tag'
    - 'Second Tag'
    - 'Third Tag'",
        );
    }

    public function test_command_with_media_input()
    {
        $this->directory('_media/test-publication');
        $this->file('_media/test-publication/image.jpg');
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'media',
                'name' => 'media',
            ],
            ],
        ]);

        $this->artisan('make:publication test-publication')
             ->expectsQuestion('Which file would you like to use?', '_media/test-publication/image.jpg')
             ->assertExitCode(0);

        $this->assertDatedPublicationExists();
        $this->assertCreatedPublicationMatterEquals('media: _media/test-publication/image.jpg');
    }

    public function test_media_input_selects_the_right_file()
    {
        $this->directory('_media/test-publication');
        $this->file('_media/test-publication/foo.jpg');
        $this->file('_media/test-publication/bar.png');
        $this->file('_media/test-publication/baz.svg');

        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'media',
                'name' => 'media',
            ],
            ],
        ]);

        $this->artisan('make:publication test-publication')
             ->expectsQuestion('Which file would you like to use?', '_media/test-publication/bar.png')
             ->assertExitCode(0);

        $this->assertDatedPublicationExists();
        $this->assertCreatedPublicationMatterEquals('media: _media/test-publication/bar.png');
    }

    public function test_command_with_single_tag_input()
    {
        $this->file('tags.yml', json_encode([
            'test-publication' => ['foo', 'bar', 'baz'],
        ]));
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'tag',
                'name' => 'tag',
            ],
            ],
        ]);

        $this->artisan('make:publication test-publication')
             ->expectsQuestion('Which tag would you like to use?', 'foo')
             ->assertExitCode(0);

        $this->assertDatedPublicationExists();

        $this->assertCreatedPublicationMatterEquals("tag:\n    - foo");
    }

    public function test_command_with_multiple_tag_inputs()
    {
        $this->file('tags.yml', json_encode([
            'test-publication' => ['foo', 'bar', 'baz'],
        ]));
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'tag',
                'name' => 'tags',
            ],
            ],
        ]);

        /** @noinspection PhpParamsInspection as array is allowed by this method */
        $this->artisan('make:publication test-publication')
             ->expectsQuestion('Which tag would you like to use?', ['foo', 'bar'])
             ->assertExitCode(0);

        $this->assertDatedPublicationExists();
        $this->assertCreatedPublicationMatterEquals('tags:
    - foo
    - bar');
    }

    public function test_media_input_with_no_images()
    {
        $this->throwOnConsoleException(false);
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'media',
                'name' => 'media',
            ],
            ],
        ]);

        $this->artisan('make:publication test-publication')
             ->expectsOutput('Warning: No media files found in directory _media/test-publication/')
             ->expectsConfirmation('Would you like to skip this field?')
             ->expectsOutput('Error: Unable to locate any media files for this publication type')
             ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('test-publication/2022-01-01-000000.md'));
    }

    public function test_media_input_with_no_files_but_skips()
    {
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'media',
                'name' => 'media',
            ],
            ],
        ]);

        $this->artisan('make:publication test-publication')
             ->expectsOutput('Warning: No media files found in directory _media/test-publication/')
             ->expectsConfirmation('Would you like to skip this field?', 'yes')
             ->doesntExpectOutput('Error: Unable to locate any media files for this publication type')
             ->assertExitCode(0);

        $this->assertDatedPublicationExists();
        $this->assertEquals(
            <<<'MARKDOWN'
            ---
            __createdAt: 2022-01-01T00:00:00+00:00
            ---
            
            ## Write something awesome.
            
            
            MARKDOWN, $this->getDatedPublicationContents());
    }

    public function test_tag_input_with_no_tags()
    {
        $this->throwOnConsoleException(false);
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'tag',
                'name' => 'tag',
            ],
            ],
        ]);

        $this->artisan('make:publication test-publication')
             ->expectsOutput('Warning: No tags found in tags.yml')
             ->expectsConfirmation('Would you like to skip this field?')
             ->expectsOutput('Error: Unable to locate any tags for this publication type')
             ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('test-publication/2022-01-01-000000.md'));
    }

    public function test_tag_input_with_no_tags_but_skips()
    {
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'tag',
                'name' => 'tag',
            ],
            ],
        ]);

        $this->artisan('make:publication test-publication')
             ->expectsOutput('Warning: No tags found in tags.yml')
             ->expectsConfirmation('Would you like to skip this field?', 'yes')
             ->doesntExpectOutput('Error: Unable to locate any tags for this publication type')
             ->assertExitCode(0);

        $this->assertDatedPublicationExists();
        $this->assertEquals(
            <<<'MARKDOWN'
            ---
            __createdAt: 2022-01-01T00:00:00+00:00
            ---
            
            ## Write something awesome.
            
            
            MARKDOWN, $this->getDatedPublicationContents());
    }

    public function test_handleEmptyOptionsCollection_for_required_field()
    {
        $this->throwOnConsoleException(false);
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'tag',
                'name' => 'tag',
                'rules' => ['required'],
            ],
            ],
        ]);

        $this->artisan('make:publication test-publication')
            ->doesntExpectOutput('Warning: No tags found in tags.yml')
            ->expectsOutput('Error: Unable to create publication: No tags found in tags.yml')
            ->assertExitCode(1);
    }

    public function test_with_custom_validation_rules()
    {
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'integer',
                'name' => 'integer',
                'rules' => ['max:10'],
            ],
            ],
        ]);

        $this->artisan('make:publication test-publication')
            ->expectsQuestion('Enter data for field </>[<comment>integer</comment>]', 'string')
            ->expectsOutput('The integer must be an integer.')
            ->expectsQuestion('Enter data for field </>[<comment>integer</comment>]', 15)
            ->expectsOutput('The integer must not be greater than 10.')
            ->expectsQuestion('Enter data for field </>[<comment>integer</comment>]', 5)
            ->assertExitCode(0);

        $this->assertDatedPublicationExists();
        $this->assertCreatedPublicationMatterEquals('integer: 5');
    }

    public function test_with_skipping_inputs()
    {
        $this->makeSchemaFile([
            'canonicalField' => '__createdAt',
            'fields'         =>  [[
                'type' => 'string',
                'name' => 'string',
            ],
            ],
        ]);

        $this->artisan('make:publication test-publication')
            ->expectsQuestion('Enter data for field </>[<comment>string</comment>]', '')
            ->expectsOutput(' > Skipping field string')
            ->assertExitCode(0);

        $this->assertDatedPublicationExists();
        $this->assertEquals(
            <<<'MARKDOWN'
            ---
            __createdAt: 2022-01-01T00:00:00+00:00
            ---
            
            ## Write something awesome.
            
            
            MARKDOWN, $this->getDatedPublicationContents());
    }

    protected function makeSchemaFile(array $merge = []): void
    {
        file_put_contents(
            Hyde::path('test-publication/schema.json'),
            json_encode(array_merge([
                'name'           => 'Test Publication',
                'canonicalField' => 'title',
                'detailTemplate' => 'detail',
                'listTemplate'   => 'list',
                'pageSize'       => 10,
                'sortField'      => '__createdAt',
                'sortAscending'  => true,
                'fields'         =>  [
                    [
                        'name' => 'title',
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
            __createdAt: 2022-01-01T00:00:00+00:00
            title: 'Hello World'
            ---
            
            ## Write something awesome.
            
            
            MARKDOWN, file_get_contents(Hyde::path('test-publication/hello-world.md'))
        );
    }

    protected function assertDatedPublicationExists(): void
    {
        $this->assertFileExists(Hyde::path('test-publication/2022-01-01-000000.md'));
    }

    protected function assertCreatedPublicationEquals(string $expected): void
    {
        $this->assertEquals($expected, $this->getDatedPublicationContents());
    }

    protected function assertCreatedPublicationMatterEquals(string $expected): void
    {
        $this->assertEquals(
            <<<MARKDOWN
            ---
            __createdAt: 2022-01-01T00:00:00+00:00
            $expected
            ---
            
            ## Write something awesome.
            
            
            MARKDOWN, $this->getDatedPublicationContents());
    }

    protected function getDatedPublicationContents(): string
    {
        return file_get_contents(Hyde::path('test-publication/2022-01-01-000000.md'));
    }
}
