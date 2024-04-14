<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Foundation\HydeKernel::getBasePath
 * @covers \Hyde\Foundation\HydeKernel::setBasePath
 * @covers \Hyde\Foundation\HydeKernel::path
 */
class HydeBasePathCanBeChangedTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    public function testHydeBasePathCanBeChanged()
    {
        $basePath = Hyde::getBasePath();

        Hyde::setBasePath('/foo/bar');

        $this->assertSame('/foo/bar', Hyde::getBasePath());
        $this->assertSame('/foo/bar', Hyde::path());

        Hyde::setBasePath($basePath);
    }
}
