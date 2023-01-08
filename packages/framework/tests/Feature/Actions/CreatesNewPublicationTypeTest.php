<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use function file_get_contents;
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
            false,
            10
        );
        $creator->create();

        $this->assertFileExists(Hyde::path('test-publication/schema.json'));
        $this->assertSame(<<<'JSON'
            {
                "name": "Test Publication",
                "canonicalField": "canonical",
                "detailTemplate": "detail.blade.php",
                "listTemplate": "list.blade.php",
                "pagination": {
                    "sortField": "sort",
                    "sortAscending": false,
                    "pageSize": 10
                },
                "fields": []
            }
            JSON, file_get_contents(Hyde::path('test-publication/schema.json'))
        );
    }

    public function test_create_with_default_parameters()
    {
        $creator = new CreatesNewPublicationType(
            'Test Publication',
            new Collection(),
        );
        $creator->create();

        $this->assertFileExists(Hyde::path('test-publication/schema.json'));
        $this->assertSame(<<<'JSON'
            {
                "name": "Test Publication",
                "canonicalField": "__createdAt",
                "detailTemplate": "detail.blade.php",
                "listTemplate": "list.blade.php",
                "pagination": {
                    "sortField": "__createdAt",
                    "sortAscending": true,
                    "pageSize": 25
                },
                "fields": []
            }
            JSON, file_get_contents(Hyde::path('test-publication/schema.json'))
        );
    }

    public function test_it_creates_list_and_detail_pages()
    {
        $creator = new CreatesNewPublicationType(
            'Test Publication',
            new Collection(),
            'canonical',
        );
        $creator->create();

        $this->assertFileExists(Hyde::path('test-publication/detail.blade.php'));
        $this->assertFileExists(Hyde::path('test-publication/list.blade.php'));
    }
}
