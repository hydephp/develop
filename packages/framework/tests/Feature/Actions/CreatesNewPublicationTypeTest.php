<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\CreatesNewPublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\Actions\CreatesNewPublicationType
 *
 * @see \Hyde\Console\Commands\MakePublicationTypeCommand
 */
class CreatesNewPublicationTypeTest extends TestCase
{
    protected function tearDown(): void
    {
        Filesystem::deleteDirectory('test-publication');

        parent::tearDown();
    }

    public function test_it_creates_a_new_publication_type()
    {
        $creator = new CreatesNewPublicationType(
            'Test Publication',
            new Collection(),
            'canonical',
            'sort',
            true,
            true,
            10
        );
        $creator->create();

        $this->assertFileExists(Hyde::path('test-publication/schema.json'));

        $this->assertSame(<<<'JSON'
            {
                "name": "Test Publication",
                "canonicalField": "canonical",
                "detailTemplate": "test-publication_detail",
                "listTemplate": "test-publication_list",
                "pagination": {
                    "sortField": "sort",
                    "sortAscending": true,
                    "prevNextLinks": true,
                    "pageSize": 10
                },
                "fields": []
            }
            JSON, file_get_contents(Hyde::path('test-publication/schema.json'))
        );
    }
}
