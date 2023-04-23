<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Markdown\Models\MarkdownDocument;
use Hyde\Publications\Actions\SeedsPublicationFiles;
use Hyde\Publications\Models\PublicationFieldDefinition;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Pages\PublicationPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Publications\Actions\SeedsPublicationFiles
 */
class SeedsPublicationFilesTest extends TestCase
{
    protected PublicationType $pubType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory('test-publication');
        $this->pubType = new PublicationType('Test Publication');
    }

    public function testCreate()
    {
        $action = new SeedsPublicationFiles($this->pubType);
        $action->create();

        $files = $this->getPublicationFiles();
        $this->assertFileExists($files[0]);
        $this->assertCount(1, $files);
    }

    public function testCreateMany()
    {
        $action = new SeedsPublicationFiles($this->pubType, 3);
        $action->create();

        $files = $this->getPublicationFiles();
        $this->assertCount(3, $files);
    }

    public function testWithArrayType()
    {
        $this->updateSchema('array', 'tags');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertNotEmpty($publication->matter('tags'));
        $this->assertIsArray($publication->matter('tags'));
        $this->assertSame(0, key($publication->matter('tags')));
        $this->assertIsString($publication->matter('tags')[0]);
    }

    public function testWithBooleanType()
    {
        $this->updateSchema('boolean', 'published');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertIsBool($publication->matter('published'));
    }

    public function testWithDateTimeType()
    {
        $this->updateSchema('datetime', 'published_at');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertIsString($publication->matter('published_at'));
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $publication->matter('published_at'));
    }

    public function testWithFloatType()
    {
        $this->updateSchema('float', 'price');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertIsFloat($publication->matter('price'));
    }

    public function testWithMediaType()
    {
        $this->updateSchema('media', 'media');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertIsString($publication->matter('media'));
        $this->assertStringStartsWith('https://picsum.photos/id/', $publication->matter('media'));
    }

    public function testWithIntegerType()
    {
        $this->updateSchema('integer', 'views');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertIsInt($publication->matter('views'));
    }

    public function testWithStringType()
    {
        $this->updateSchema('string', 'title');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertNotEmpty($publication->matter('title'));
    }

    public function testWithTagType()
    {
        $tags = ['foo', 'bar', 'baz'];
        $page = new PublicationPage('test', ['tag' => $tags], type: $this->pubType);
        $page->save();
        $this->pubType->fields = collect([
            new PublicationFieldDefinition('tag', 'tag'),
        ]);
        $this->pubType->save();
        (new SeedsPublicationFiles($this->pubType))->create();

        unlink($page->getSourcePath());
        $publication = $this->firstPublication();

        $this->assertNotEmpty($publication->matter('tag'));
        $this->assertIsString($publication->matter('tag'));
        $this->assertTrue(in_array($publication->matter('tag'), $tags));
    }

    public function testWithTextType()
    {
        $this->updateSchema('text', 'description');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertNotEmpty($publication->matter('description'));
        $this->assertIsString($publication->matter('description'));
        $this->assertTrue(substr_count($publication->matter('description'), "\n") >= 1);
    }

    public function testWithUrlType()
    {
        $this->updateSchema('url', 'url');
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertIsString($publication->matter('url'));
        $this->assertStringStartsWith('http', $publication->matter('url'));
    }

    public function testWithCanonicalDefinition()
    {
        $this->updateSchema('string', 'title');
        $this->pubType->canonicalField = 'title';
        $this->pubType->save();
        (new SeedsPublicationFiles($this->pubType))->create();

        $publication = $this->firstPublication();

        $this->assertNotEmpty($publication->matter('title'));
        $this->assertIsString($publication->matter('title'));
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

    protected function updateSchema(string $type, string $name): void
    {
        $this->pubType->fields = collect([
            new PublicationFieldDefinition($type, $name),
        ]);
        $this->pubType->save();
    }
}
