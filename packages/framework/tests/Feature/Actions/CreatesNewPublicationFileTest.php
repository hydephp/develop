<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\CreatesNewPublicationFile;
use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Rgasch\Collection\Collection;

/**
 * @covers \Hyde\Framework\Actions\CreatesNewPublicationFile
 */
class CreatesNewPublicationFileTest extends TestCase
{
    public function testCreate()
    {
        $pubType = PublicationType::fromFile(Hyde::path('tests/fixtures/test-publication-schema.json'));
        $fieldData = Collection::make([
            'test' => new PublicationField('string', 'test', 1, 10),
        ]);

        $creator = new CreatesNewPublicationFile($pubType, $fieldData);
        $creator->create();

        $this->assertTrue(File::exists(Hyde::path('tests/fixtures/test-publication/hello-world.md')));
        $this->assertSame('TODO', file_get_contents(Hyde::path('tests/fixtures/test-publication/hello-world.md')));
    }
}
