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

        $this->assertFileExists('name/schema.json');
        $this->assertStringContainsString('"name": "name"', $creator->getResult());
        $this->assertStringContainsString('"canonicalField": "canonical"', $creator->getResult());
        $this->assertStringContainsString('"sortField": "sort"', $creator->getResult());
        $this->assertStringContainsString('"sortDirection": "asc"', $creator->getResult());
        $this->assertStringContainsString('"pageSize": 10', $creator->getResult());
        $this->assertStringContainsString('"prevNextLinks": true', $creator->getResult());
        $this->assertStringContainsString('"detailTemplate": "name_detail"', $creator->getResult());
        $this->assertStringContainsString('"listTemplate": "name_list"', $creator->getResult());

        deleteDirectory(Hyde::path('name'));
    }
}
