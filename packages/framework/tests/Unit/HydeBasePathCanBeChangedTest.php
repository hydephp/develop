<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * Class HydeBasePathCanBeChangedTest.
 *
 * @covers \Hyde\Framework\HydeKernel::getBasePath
 * @covers \Hyde\Framework\HydeKernel::setBasePath
 * @covers \Hyde\Framework\HydeKernel::path
 */
class HydeBasePathCanBeChangedTest extends TestCase
{
    protected string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        if (! isset($this->basePath)) {
            $this->basePath = Hyde::getBasePath();
        }
    }

    protected function tearDown(): void
    {
        Hyde::setBasePath($this->basePath);

        parent::tearDown();
    }

    public function test_hyde_base_path_can_be_changed()
    {
        Hyde::setBasePath('/foo/bar');
        $this->assertEquals('/foo/bar', Hyde::getBasePath());
        $this->assertEquals('/foo/bar', Hyde::path());
    }
}
