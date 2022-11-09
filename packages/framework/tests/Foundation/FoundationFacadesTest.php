<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Foundation;

use Hyde\Foundation\Facades\FileCollection;
use Hyde\Foundation\HydeKernel;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\Facades\FileCollection
 */
class FoundationFacadesTest extends TestCase
{
    public function test_file_collection_facade()
    {
        $this->assertSame(
            HydeKernel::getInstance()->files(),
            FileCollection::getInstance()
        );

        $this->assertEquals(
            Hyde::files()->getSourceFiles(),
            FileCollection::getSourceFiles()
        );
    }

    public function test_facade_roots()
    {
        $this->assertSame(
            FileCollection::getInstance(),
            FileCollection::getFacadeRoot()
        );
    }
}
