<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Rgasch\Collection\Collection;

use function copy;
use function file_put_contents;
use function mkdir;

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
            'test-publication' => PublicationType::get('test-publication')
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
               PublicationService::getPublicationData('test-publication/foo.md')
            ]),
            PublicationService::getPublicationsForPubType(PublicationType::get('test-publication'))
        );
    }

    protected function createPublicationType(): void
    {
        copy(Hyde::path('tests/fixtures/test-publication-schema.json'), Hyde::path('test-publication/schema.json'));
    }

    protected function createPublication(): void
    {
        file_put_contents(
            Hyde::path('test-publication/foo.md'),
            "---\n__canonical: canonical\n__createdAt: 2022-11-16 11:32:52\nfoo: bar\n---\n\nHello World!\n"
        );
    }
}
