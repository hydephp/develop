<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\SeedsPublicationFiles;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\SeedsPublicationFiles
 */
class SeedsPublicationFilesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->directory('test-publication');
        $this->setupTestPublication();
    }

    public function testCreate()
    {
        $action = new SeedsPublicationFiles(PublicationType::get('test-publication'));
        $action->create();

        $this->assertFileExists($this->getPublicationFiles()[0]);
    }

    protected function getPublicationFiles(): array
    {
        return glob(Hyde::path('test-publication/*.md'));
    }
}
