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
    public function test_it_creates_a_new_publication_type()
    {
        $creator = new CreatesNewPublicationType('name', new Collection(), 'canonical', 'sort', true, true, 10);
        $creator->create();

        $this->assertFileExists(Hyde::path('name/schema.json'));

        $result = file_get_contents(Hyde::path('name/schema.json'));
        $this->assertSame(<<<'JSON'
            {
                "name": "name",
                "canonicalField": "canonical",
                "detailTemplate": "name_detail",
                "listTemplate": "name_list",
                "pagination": {
                    "sortField": "sort",
                    "sortAscending": true,
                    "prevNextLinks": true,
                    "pageSize": 10
                },
                "fields": []
            }
            JSON, $result);
        Filesystem::deleteDirectory('name');
    }
}
