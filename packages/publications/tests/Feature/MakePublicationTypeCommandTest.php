<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Facades\Filesystem;
use Hyde\Publications\Models\PublicationTags;
use Hyde\Publications\Concerns\PublicationFieldTypes;
use Hyde\Publications\Commands\Helpers\InputStreamHandler;

/**
 * @covers \Hyde\Publications\Commands\MakePublicationTypeCommand
 * @covers \Hyde\Publications\Actions\CreatesNewPublicationType
 */
class MakePublicationTypeCommandTest extends TestCase
{
    protected const selectPageSizeQuestion = 'How many links should be shown on the listing page? <fg=gray>(any value above 0 will enable <href=https://docs.hydephp.com/search?query=pagination>pagination</>)</>';
    protected const selectCanonicalNameQuestion = 'Choose a canonical name field <fg=gray>(this will be used to generate filenames, so the values need to be unique)</>';
    protected const expectedEnumCases = ['String', 'Datetime', 'Boolean', 'Integer', 'Float', 'Array', 'Media', 'Text', 'Tag', 'Url'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->throwOnConsoleException();
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
                'Array',
                'Media',
                'Text',
                'Tag',
                'Url',
            ], true)
            ->expectsConfirmation('Field #1 added! Add another field?')

            ->expectsChoice(self::selectCanonicalNameQuestion, 'publication-title', [
                '__createdAt',
                'publication-title',
            ])

            ->expectsChoice('Choose the field you wish to sort by', '__createdAt', [
                '__createdAt',
                'publication-title',
            ])
            ->expectsChoice('Choose the sort direction', 'Ascending', [
                'Ascending',
                'Descending',
            ])
            ->expectsQuestion(self::selectPageSizeQuestion, 10)

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

        $this->assertStringContainsString('paginator', file_get_contents(Hyde::path('test-publication/list.blade.php')));
    }

    public function test_with_default_values()
    {
        // When running this command with the no-interaction flag in an actual console, no questions are asked.
        // However, when running it in a test, the questions are still asked, presumably due to a vendor bug.

        $this->withoutMockingConsoleOutput();

        $this->assertSame(0, $this->artisan('make:publicationType "Test Publication" --no-interaction'));

        $this->assertFileExists(Hyde::path('test-publication/schema.json'));
        $this->assertEquals(
            <<<'JSON'
            {
                "name": "Test Publication",
                "canonicalField": "__createdAt",
                "detailTemplate": "detail.blade.php",
                "listTemplate": "list.blade.php",
                "sortField": "__createdAt",
                "sortAscending": true,
                "pageSize": 0,
                "fields": [
                    {
                        "type": "string",
                        "name": "example-field"
                    }
                ]
            }
            JSON,
            file_get_contents(Hyde::path('test-publication/schema.json'))
        );

        $this->assertFileExists(Hyde::path('test-publication/detail.blade.php'));
        $this->assertFileExists(Hyde::path('test-publication/list.blade.php'));

        $this->assertStringNotContainsString('paginator', file_get_contents(Hyde::path('test-publication/list.blade.php')));
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

            ->expectsChoice(self::selectCanonicalNameQuestion, 'foo', [
                '__createdAt',
                'bar',
                'foo',
            ])

            ->expectsChoice('Choose the field you wish to sort by', '__createdAt', ['__createdAt', 'foo', 'bar'])
            ->expectsChoice('Choose the sort direction', 'Ascending', ['Ascending', 'Descending'])
            ->expectsQuestion(self::selectPageSizeQuestion, 0)

            ->assertExitCode(0);
    }

    public function test_with_existing_file_of_the_same_name()
    {
        $this->throwOnConsoleException(false);

        $this->file('test-publication');

        $this->artisan('make:publicationType "Test Publication"')
            ->expectsOutput('Error: Storage path [test-publication] already exists')
            ->assertExitCode(1);
    }

    public function test_with_existing_publication_of_the_same_name()
    {
        $this->throwOnConsoleException(false);

        $this->directory('test-publication');
        $this->file('test-publication/foo');

        $this->artisan('make:publicationType "Test Publication"')
             ->expectsOutput('Error: Storage path [test-publication] already exists')
             ->assertExitCode(1);
    }

    public function testWithTagFieldInput()
    {
        $this->directory('test-publication');

        (new PublicationTags())->addTags(['foo', 'bar', 'baz'])->save();

        $this->artisan('make:publicationType "Test Publication"')
            ->expectsQuestion('Enter name for field #1', 'MyTag')
            ->expectsChoice('Enter type for field #1', 'Tag',
                self::expectedEnumCases)
            ->expectsConfirmation('Field #1 added! Add another field?')
            ->expectsChoice(self::selectCanonicalNameQuestion, '__createdAt', ['__createdAt'])
            ->expectsChoice('Choose the field you wish to sort by', '__createdAt', ['__createdAt'])
            ->expectsChoice('Choose the sort direction', 'Ascending', ['Ascending', 'Descending'])
            ->expectsQuestion(self::selectPageSizeQuestion, 0)

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
                "pageSize": 0,
                "fields": [
                    {
                        "type": "tag",
                        "name": "my-tag"
                    }
                ]
            }
            JSON,
            'test-publication/schema.json');

        unlink(Hyde::path('tags.yml'));
    }
}
