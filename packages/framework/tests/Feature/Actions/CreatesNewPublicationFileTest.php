<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\CreatesNewPublicationFile;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\CreatesNewPublicationFile
 */
class CreatesNewPublicationFileTest extends TestCase
{
    public function testCreate()
    {
        $pubType   = PublicationType::fromFile(Hyde::path('tests/fixtures/test-publication-schema.json'));
        $fieldData = (object) [];
        $creator   = new CreatesNewPublicationFile($pubType, $fieldData);
        $creator->create();
    }
}
