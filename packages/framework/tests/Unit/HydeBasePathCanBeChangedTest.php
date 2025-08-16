<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\HydeKernel::class)]
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
