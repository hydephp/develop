<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\SeedsPublicationFiles;
use Hyde\Framework\Features\Publications\Models\PublicationFieldType;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Markdown\Models\MarkdownDocument;
use Hyde\Testing\TestCase;
use function key;

/**
 * @covers \Hyde\Framework\Actions\SeedsPublicationFiles
 */
class SeedsPublicationFilesTest extends TestCase
{
    protected PublicationType $pubType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory('test-publication');
        $this->setupTestPublication();
        $this->pubType = PublicationType::get('test-publication');
    }

    public function testCreate()
    {
        $action = new SeedsPublicationFiles($this->pubType);
        $action->create();

        $this->assertFileExists($this->getPublicationFiles()[0]);
    }

    // array
    public function testWithArrayType()
    {
        $this->updateSchema('array', 'tags');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertBaseline($publication);
        $this->assertNotEmpty($publication->matter('tags'));
        $this->assertIsArray($publication->matter('tags'));
        $this->assertSame(0, key($publication->matter('tags')));
        $this->assertIsString($publication->matter('tags')[0]);
        $this->assertTrue(count($publication->matter('tags')) >= 3 && count($publication->matter('tags')) <= 20);
    }

    // boolean
    public function testWithBooleanType()
    {
        $this->updateSchema('boolean', 'published');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertBaseline($publication);
        $this->assertIsBool($publication->matter('published'));
    }

    // datetime
    public function testWithDateTimeType()
    {
        $this->updateSchema('datetime', 'published_at');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertBaseline($publication);
        $this->assertIsInt($publication->matter('published_at')); // Carbon parses to Unix timestamp int
    }

    // float
    public function testWithFloatType()
    {
        $this->updateSchema('float', 'price');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertBaseline($publication);
        $this->assertIsFloat($publication->matter('price'));
    }

    // image
    public function testWithImageType()
    {
        $this->updateSchema('image', 'image');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertBaseline($publication);
        $this->assertIsString($publication->matter('image'));
        $this->assertStringStartsWith('https://picsum.photos/id/', $publication->matter('image'));
    }

    // integer
    public function testWithIntegerType()
    {
        $this->updateSchema('integer', 'views');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertBaseline($publication);
        $this->assertIsInt($publication->matter('views'));
    }

    // string
    public function testWithStringType()
    {
        $this->updateSchema('string', 'title');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertBaseline($publication);
        $this->assertNotEmpty($publication->matter('title'));
    }

    // tag
    public function testWithTagType()
    {
        $this->markTestIncomplete('I am not fully sure what this is supposed to do yet.');

        $tags = ["foo", "bar", "baz"];
        $this->file('tags.json', json_encode($tags));
        $this->updateSchema('tag', 'tag', tagGroup: 'foo');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertBaseline($publication);
    }

    // text
    public function testWithTextType()
    {
        $this->updateSchema('text', 'description');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertBaseline($publication);
        $this->assertNotEmpty($publication->matter('description'));
        $this->assertIsString($publication->matter('description'));
        $this->assertTrue(substr_count($publication->matter('description'), "\n") >= 3 && substr_count($publication->matter('description'), "\n") <= 19);
    }

    // url
    public function testWithUrlType()
    {
        $this->updateSchema('url', 'url');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertBaseline($publication);
        $this->assertIsString($publication->matter('url'));
        $this->assertStringStartsWith('https://google.com?q=', $publication->matter('url'));
    }

    protected function getPublicationFiles(): array
    {
        $files = glob(Hyde::path('test-publication/*.md'));
        $this->assertNotEmpty($files, 'No publication files found.');

        return $files;
    }

    protected function firstPublication(): MarkdownDocument
    {
        return MarkdownDocument::parse(Hyde::pathToRelative($this->getPublicationFiles()[0]));
    }

    protected function updateSchema(string $type, string $name, int|string|null $min = 0, int|string|null $max = 0, ?string $tagGroup = null): void
    {
        $this->pubType->fields = [
            (new PublicationFieldType($type, $name, $min, $max, $tagGroup))->toArray(),
        ];
        $this->pubType->save();
    }

    protected function assertBaseline(MarkdownDocument $publication): void
    {
        $this->assertCount(2, $publication->matter()->toArray());
        $this->assertSame('## Write something awesome.', $publication->markdown()->body());
    }
}
