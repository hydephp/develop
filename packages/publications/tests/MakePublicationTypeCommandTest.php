<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing;

use function config;
use Hyde\Console\Commands\Helpers\InputStreamHandler;
use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Publications\Models\PublicationTags;
use Hyde\Publications\PublicationFieldTypes;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Publications\Commands\MakePublicationTypeCommand
 * @covers \Hyde\Publications\Actions\CreatesNewPublicationType
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
            ->expectsConfirmation('Would you like to enable pagination?', 'yes')
            ->expectsChoice('Choose the default field you wish to sort by', '__createdAt', [
                '__createdAt',
                'publication-title',
            ])
            ->expectsChoice('Choose the default sort direction', 'Ascending', [
                'Ascending',
                'Descending',
            ])
            ->expectsQuestion('Enter the page size (0 for no limit)', 10)
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
                "detailTemplate": "detail.blade.php",
                "listTemplate": "list.blade.php",
                "sortField": "__createdAt",
                "sortAscending": true,
                "pageSize": 10,
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

        $this->assertFileExists(Hyde::path('test-publication/detail.blade.php'));
        $this->assertFileExists(Hyde::path('test-publication/list.blade.php'));
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

            ->expectsConfirmation('Would you like to enable pagination?')
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

    public function testWithTagFieldInput()
    {
        $this->directory('test-publication');

        (new PublicationTags())->addTagGroups([
            'foo' => ['bar', 'baz'],
            'bar' => ['foo', 'baz'],
        ])->save();

        $this->artisan('make:publicationType "Test Publication" --use-defaults')
            ->expectsQuestion('Enter name for field #1', 'MyTag')
            ->expectsChoice('Enter type for field #1', 'Tag',
                ['String', 'Datetime', 'Boolean', 'Integer', 'Float', 'Image', 'Array', 'Text', 'Url', 'Tag'])
            ->expectsChoice('Enter tag group for field #1', 'foo', ['bar', 'foo'], true)
            ->assertSuccessful();

        $this->assertFileExists(Hyde::path('test-publication/schema.json'));
        $this->assertFileEqualsString(
            <<<'JSON'
            {
                "name": "Test Publication",
                "canonicalField": "__createdAt",
                "detailTemplate": "detail.blade.php",
                "listTemplate": "list.blade.php",
                "sortField": "__createdAt",
                "sortAscending": true,
                "pageSize": 25,
                "fields": [
                    {
                        "type": "datetime",
                        "name": "__createdAt"
                    },
                    {
                        "type": "tag",
                        "name": "my-tag",
                        "tagGroup": "foo"
                    }
                ]
            }
            JSON,
            'test-publication/schema.json');

        unlink(Hyde::path('tags.json'));
    }

    public function testWithTagFieldInputButNoTags()
    {
        config(['app.throw_on_console_exception' => false]);
        $this->directory('test-publication');

        $this->artisan('make:publicationType "Test Publication" --use-defaults')
            ->expectsQuestion('Enter name for field #1', 'MyTag')
            ->expectsChoice('Enter type for field #1', 'Tag',
                ['String', 'Datetime', 'Boolean', 'Integer', 'Float', 'Image', 'Array', 'Text', 'Url', 'Tag'], true)
            ->expectsOutput('No tag groups have been added to tags.json')
            ->expectsConfirmation('Would you like to add some tags now?')
            ->expectsOutput('Error: Can not create a tag field without any tag groups defined in tags.json')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('test-publication/schema.json'));
    }

    public function testWithTagFieldInputButNoTagsCanPromptToCreateTags()
    {
        $this->directory('test-publication');
        $this->cleanUpWhenDone('tags.json');
        InputStreamHandler::mockInput("foo\nbar\nbaz\n");

        $this->artisan('make:publicationType "Test Publication"')
            ->expectsQuestion('Enter name for field #1', 'MyTag')
            ->expectsChoice('Enter type for field #1', 'Tag',
                ['String', 'Datetime', 'Boolean', 'Integer', 'Float', 'Image', 'Array', 'Text', 'Url', 'Tag'])
            ->expectsOutput('No tag groups have been added to tags.json')
            ->expectsConfirmation('Would you like to add some tags now?', 'yes')
            ->expectsQuestion('Tag name', 'foo')
            ->expectsOutput("Okay, we're back on track!")
            ->expectsChoice('Enter tag group for field #1', 'foo', ['foo'], true)
            ->expectsConfirmation('Field #1 added! Add another field?')
            ->expectsConfirmation('Would you like to enable pagination?')
            ->expectsChoice('Choose a canonical name field (this will be used to generate filenames, so the values need to be unique)', '__createdAt', ['__createdAt'])
            ->doesntExpectOutput('Error: Can not create a tag field without any tag groups defined in tags.json')
           ->assertSuccessful();

        $this->assertCommandCalled('make:publicationTag');
        $this->assertFileExists(Hyde::path('tags.json'));
        $this->assertSame(
            json_encode(['foo' => ['foo', 'bar', 'baz']], 128),
            file_get_contents(Hyde::path('tags.json'))
        );
    }
}
