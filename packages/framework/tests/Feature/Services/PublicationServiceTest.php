<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Rgasch\Collection\Collection;

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

    public function testGetPublicationTypesWithNoTypes()
    {
        $this->assertEquals(new Collection(), PublicationService::getPublicationTypes());
    }

    public function testGetPublicationTypesWithTypes()
    {
        copy(Hyde::path('tests/fixtures/test-publication-schema.json'), Hyde::path('test-publication/schema.json'));

        $this->assertEquals(new Collection([
            'test-publication' => PublicationType::get('test-publication')
        ]), PublicationService::getPublicationTypes());
    }
}
