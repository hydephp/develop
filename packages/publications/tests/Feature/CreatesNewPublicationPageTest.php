<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use RuntimeException;
use Hyde\Testing\TestCase;
use Illuminate\Support\Str;
use Hyde\Facades\Filesystem;
use Illuminate\Support\Carbon;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Collection;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Models\PublicationFieldValue;
use Hyde\Publications\Concerns\PublicationFieldTypes;
use Hyde\Publications\Actions\CreatesNewPublicationPage;

/**
 * @covers \Hyde\Publications\Actions\CreatesNewPublicationPage
 */
class CreatesNewPublicationPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2022));
    }

    protected function tearDown(): void
    {
        Filesystem::deleteDirectory('test-publication');

        parent::tearDown();
    }

    public function testCreate()
    {
        $pubType = new PublicationType(
            'Test Publication',
            'title',
            fields: [['type' => 'string', 'name' => 'title']],
        );

        $fieldData = Collection::make([
            'title' => new PublicationFieldValue(PublicationFieldTypes::String, 'Hello World'),
        ]);

        (new CreatesNewPublicationPage($pubType, $fieldData))->create();

        $this->assertFileExists(Hyde::path('test-publication/hello-world.md'));
        $this->assertEquals(<<<'MARKDOWN'
            ---
            __createdAt: 2022-01-01T00:00:00+00:00
            title: 'Hello World'
            ---

            ## Write something awesome.


            MARKDOWN, file_get_contents(Hyde::path('test-publication/hello-world.md')));
    }

    public function testWithTextType()
    {
        $pubType = $this->makePublicationType([[
            'type' => 'text',
            'name' => 'description',
        ]]);

        $fieldData = Collection::make(['description' => new PublicationFieldValue(PublicationFieldTypes::Text, <<<'TEXT'
            This is a description
            It can be multiple lines.
            TEXT),
        ]);

        (new CreatesNewPublicationPage($pubType, $fieldData))->create();

        $this->assertFileExists(Hyde::path('test-publication/2022-01-01-000000.md'));
        $this->assertEquals(<<<'MARKDOWN'
            ---
            __createdAt: 2022-01-01T00:00:00+00:00
            description: |
                This is a description
                It can be multiple lines.
            ---

            ## Write something awesome.


            MARKDOWN, file_get_contents(Hyde::path('test-publication/2022-01-01-000000.md')));
    }

    public function testWithArrayType()
    {
        $pubType = $this->makePublicationType([[
            'type' => 'array',
            'name' => 'tags',
        ]]);

        $fieldData = Collection::make([
            'tags' => new PublicationFieldValue(PublicationFieldTypes::Tag, ['tag1', 'tag2', 'foo bar']),
        ]);

        (new CreatesNewPublicationPage($pubType, $fieldData))->create();

        $this->assertFileExists(Hyde::path('test-publication/2022-01-01-000000.md'));
        $this->assertEquals(<<<'MARKDOWN'
            ---
            __createdAt: 2022-01-01T00:00:00+00:00
            tags:
                - tag1
                - tag2
                - 'foo bar'
            ---

            ## Write something awesome.


            MARKDOWN, file_get_contents(Hyde::path('test-publication/2022-01-01-000000.md')));
    }

    public function testCreateWithoutSupplyingCanonicalField()
    {
        $pubType = new PublicationType(
            'Test Publication',
            'title',
            fields: [['type' => 'string', 'name' => 'title']],
        );

        $fieldData = Collection::make();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Could not find field value for 'title' which is required as it's the type's canonical field");
        (new CreatesNewPublicationPage($pubType, $fieldData))->create();
    }

    public function testCreateWithoutSupplyingRequiredField()
    {
        $pubType = $this->makePublicationType([['type' => 'string', 'name' => 'title']]);

        $fieldData = Collection::make();

        (new CreatesNewPublicationPage($pubType, $fieldData))->create();

        // Since the inputs are collected by the command, with the shipped code this should never happen.
        // If a developer is using the action directly, it's their responsibility to ensure the data is valid.

        $this->assertFileExists(Hyde::path('test-publication/2022-01-01-000000.md'));
        $this->assertEquals(<<<'MARKDOWN'
            ---
            __createdAt: 2022-01-01T00:00:00+00:00
            ---

            ## Write something awesome.


            MARKDOWN, file_get_contents(Hyde::path('test-publication/2022-01-01-000000.md')));
    }

    public function testItCreatesValidYaml()
    {
        $pubType = $this->makePublicationType([
            ['type' => 'string', 'name' => 'title'],
            ['type' => 'text', 'name' => 'description'],
            ['type' => 'array', 'name' => 'tags'],
        ]);

        $fieldData = Collection::make([
            'title' => new PublicationFieldValue(PublicationFieldTypes::String, 'Hello World'),
            'description' => new PublicationFieldValue(PublicationFieldTypes::Text, "This is a description.\nIt can be multiple lines.\n"),
            'tags' => new PublicationFieldValue(PublicationFieldTypes::Tag, ['tag1', 'tag2', 'foo bar']),
        ]);

        (new CreatesNewPublicationPage($pubType, $fieldData))->create();

        $this->assertFileExists(Hyde::path('test-publication/2022-01-01-000000.md'));
        $contents = file_get_contents(Hyde::path('test-publication/2022-01-01-000000.md'));
        $this->assertEquals(<<<'MARKDOWN'
            ---
            __createdAt: 2022-01-01T00:00:00+00:00
            title: 'Hello World'
            description: |
                This is a description.
                It can be multiple lines.
            tags:
                - tag1
                - tag2
                - 'foo bar'
            ---

            ## Write something awesome.


            MARKDOWN, $contents
        );

        $this->assertSame([
            '__createdAt' => 1640995200,
            'title' => 'Hello World',
            'description' => "This is a description.\nIt can be multiple lines.\n",
            'tags' => ['tag1', 'tag2', 'foo bar'],
        ], Yaml::parse(Str::between($contents, '---', '---')));
    }

    protected function makePublicationType(array $fields): PublicationType
    {
        return new PublicationType('Test Publication', '__createdAt', fields: $fields);
    }
}
