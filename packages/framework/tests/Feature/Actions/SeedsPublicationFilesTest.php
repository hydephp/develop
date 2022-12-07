<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

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
}
