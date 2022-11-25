<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use ErrorException;
use function copy;
use function file_put_contents;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Hyde;
use Hyde\Pages\PublicationPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use function mkdir;
use Rgasch\Collection\Collection;

/**
 * @covers \Hyde\Framework\Features\Publications\PublicationService
 */
class PublicationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        mkdir(Hyde::path('test-publication'));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(Hyde::path('test-publication'));

        parent::tearDown();
    }

    public function testGetPublicationTypes()
    {
        $this->assertEquals(new Collection(), PublicationService::getPublicationTypes());
    }

    public function testGetPublicationTypesWithTypes()
    {
        $this->createPublicationType();

        $this->assertEquals(new Collection([
            'test-publication' => PublicationType::get('test-publication'),
        ]), PublicationService::getPublicationTypes());
    }

    public function testGetPublicationsForPubType()
    {
        $this->createPublicationType();

        $this->assertEquals(
            new Collection(),
            PublicationService::getPublicationsForPubType(PublicationType::get('test-publication'))
        );
    }

    public function testGetPublicationsForPubTypeWithPublications()
    {
        $this->createPublicationType();
        $this->createPublication();

        $this->assertEquals(
            new Collection([
                PublicationService::parsePublicationFile('test-publication/foo.md'),
            ]),
            PublicationService::getPublicationsForPubType(PublicationType::get('test-publication'))
        );
    }

    public function testGetPublicationsForPubTypeOnlyContainsInstancesOfPublicationPage()
    {
        $this->createPublicationType();
        $this->createPublication();

        $this->assertContainsOnlyInstancesOf(
            PublicationPage::class,
            PublicationService::getPublicationsForPubType(PublicationType::get('test-publication'))
        );
    }

    public function testParsePublicationFile()
    {
        $this->createPublicationType();
        $this->createPublication();

        $file = PublicationService::parsePublicationFile('test-publication/foo');
        $this->assertInstanceOf(PublicationPage::class, $file);
        $this->assertEquals('test-publication/foo', $file->getIdentifier());
    }

    public function testParsePublicationFileWithFileExtension()
    {
        $this->createPublicationType();
        $this->createPublication();

        $this->assertEquals(
            PublicationService::parsePublicationFile('test-publication/foo'),
            PublicationService::parsePublicationFile('test-publication/foo.md')
        );
    }

    public function testPublicationTypeExists()
    {
        $this->createPublicationType();

        $this->assertTrue(PublicationService::publicationTypeExists('test-publication'));
        $this->assertFalse(PublicationService::publicationTypeExists('foo'));
    }

    protected function createPublicationType(): void
    {
        copy(
            Hyde::path('tests/fixtures/test-publication-schema.json'),
            Hyde::path('test-publication/schema.json')
        );
    }

    protected function createPublication(): void
    {
        file_put_contents(
            Hyde::path('test-publication/foo.md'),
            "---\n__canonical: canonical\n__createdAt: 2022-11-16 11:32:52\nfoo: bar\n---\n\nHello World!\n"
        );
    }
}
