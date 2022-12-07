<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Markdown\Models\MarkdownDocument;
use function explode;
use function file_get_contents;
use Hyde\Framework\Actions\SeedsPublicationFiles;
use Hyde\Framework\Features\Publications\Models\PublicationFieldType;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use PHPUnit\Framework\ExpectationFailedException;
use function key;
use function str_ends_with;
use function str_replace;

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

        $this->assertFileExists($this->firstPublicationFilePath());
    }

    public function testWithStringType()
    {
        $this->updateSchema('string', 'title');
        (new SeedsPublicationFiles($this->pubType))->create();
        $publication = $this->firstPublication();

        $this->assertCount(2, $publication->matter()->toArray());
        $this->assertNotEmpty($publication->matter('title'));
        $this->assertSame('## Write something awesome.', $publication->markdown()->body());
    }

    public function testWithArrayType()
    {
        $this->updateSchema('array', 'tags');
        (new SeedsPublicationFiles($this->pubType))->create();
        $publication = $this->firstPublication();

        $this->assertCount(2, $publication->matter()->toArray());
        $this->assertNotEmpty($publication->matter('tags'));
        $this->assertIsArray($publication->matter('tags'));
        $this->assertSame(0, key($publication->matter('tags')));
        $this->assertIsString($publication->matter('tags')[0]);
        $this->assertTrue(count($publication->matter('tags')) >= 3 && count($publication->matter('tags')) <= 20);
        $this->assertSame('## Write something awesome.', $publication->markdown()->body());
    }

    public function testWithBooleanType()
    {
        $this->updateSchema('boolean', 'published');
        (new SeedsPublicationFiles($this->pubType))->create();
        $publication = $this->firstPublication();

        $this->assertCount(2, $publication->matter()->toArray());
        $this->assertIsBool($publication->matter('published'));
        $this->assertSame('## Write something awesome.', $publication->markdown()->body());
    }

    protected function getPublicationFiles(): array
    {
        $files = glob(Hyde::path('test-publication/*.md'));
        $this->assertNotEmpty($files, 'No publication files found.');

        return $files;
    }

    protected function firstPublicationFilePath(): string
    {
        return $this->getPublicationFiles()[0];
    }

    protected function firstPublication(): MarkdownDocument
    {
        return MarkdownDocument::parse(Hyde::pathToRelative($this->firstPublicationFilePath()));
    }

    protected function updateSchema(string $type, string $name, int|string|null $min = 0, int|string|null $max = 0): void
    {
        $this->pubType->fields = [
            (new PublicationFieldType($type, $name, $min, $max))->toArray(),
        ];
        $this->pubType->save();
    }
}
