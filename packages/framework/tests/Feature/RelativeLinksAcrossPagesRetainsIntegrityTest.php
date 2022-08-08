<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

class RelativeLinksAcrossPagesRetainsIntegrityTest extends TestCase
{
    use InteractsWithDirectories;

    protected function setUp(): void
    {
        parent::setUp();

        $this->needsDirectory('_pages/nested/sub-nested');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(Hyde::path('_pages/nested'));

        parent::tearDown();
    }

    public function test_relative_links_across_pages_retains_integrity()
    {
        Hyde::touch('_pages/root.md');
        Hyde::touch('_pages/root1.md');
        Hyde::touch('_pages/nested/level1.md');
        Hyde::touch('_pages/nested/level1b.md');
        Hyde::touch('_pages/nested/sub-nested/level2.md');
    }
}
