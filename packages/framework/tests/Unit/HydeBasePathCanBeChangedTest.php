<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass('\\Hyde\\Foundation\\HydeKernel::getBasePath')]
#[CoversClass('\\Hyde\\Foundation\\HydeKernel::setBasePath')]
#[CoversClass('\\Hyde\\Foundation\\HydeKernel::path')]
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
