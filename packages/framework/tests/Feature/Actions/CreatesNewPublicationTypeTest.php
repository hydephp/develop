<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use function deleteDirectory;
use Hyde\Framework\Actions\CreatesNewPublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Rgasch\Collection\Collection;

/**
 * @covers \Hyde\Framework\Actions\CreatesNewPublicationType
 *
 * @see \Hyde\Console\Commands\MakePublicationTypeCommand
 */
class CreatesNewPublicationTypeTest extends TestCase
{
    public function test_it_creates_a_new_publication_type()
    {
        $creator = new CreatesNewPublicationType('name', new Collection(), 'canonical', 'sort', 'asc', 10, true);
        $creator->create();

        $this->assertFileExists(Hyde::path('name/schema.json'));

        $result = file_get_contents(Hyde::path('name/schema.json'));
        $this->assertStringContainsString('"name": "name"', $result);
        $this->assertStringContainsString('"canonicalField": "canonical"', $result);
        $this->assertStringContainsString('"sortField": "sort"', $result);
        $this->assertStringContainsString('"sortAscending": "asc"', $result);
        $this->assertStringContainsString('"pageSize": 10', $result);
        $this->assertStringContainsString('"prevNextLinks": true', $result);
        $this->assertStringContainsString('"detailTemplate": "name_detail"', $result);
        $this->assertStringContainsString('"listTemplate": "name_list"', $result);

        deleteDirectory(Hyde::path('name'));
    }
}
