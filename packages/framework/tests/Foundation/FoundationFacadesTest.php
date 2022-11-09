<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Foundation;

use Hyde\Foundation\Facades\FileCollectionFacade;
use Hyde\Foundation\HydeKernel;
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
            HydeKernel::getInstance()->files(),
            FileCollectionFacade::getInstance()
        );

        $this->assertEquals(
            Hyde::files()->getSourceFiles(),
            FileCollectionFacade::getSourceFiles()
        );
    }

    public function test_facade_roots()
    {
        $this->assertSame(
            FileCollectionFacade::getInstance(),
            FileCollectionFacade::getFacadeRoot()
        );
    }
}
