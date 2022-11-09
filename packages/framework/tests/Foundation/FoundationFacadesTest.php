<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Foundation;

use Hyde\Foundation\Facades\FileCollectionFacade;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\Facades\FileCollectionFacade
 */
class FoundationFacadesTest extends TestCase
{
    public function test_file_collection_facade()
    {
        $this->assertSame(
            Hyde::getInstance()->files(),
            FileCollectionFacade::getInstance()
        );

        $this->assertEquals(
            Hyde::getInstance()->files()->getSourceFiles(),
            FileCollectionFacade::getSourceFiles()
        );
    }
}
