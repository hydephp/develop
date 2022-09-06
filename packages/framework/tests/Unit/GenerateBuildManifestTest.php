<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Framework\Actions\PostBuildTasks\GenerateBuildManifest;

/**
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateBuildManifest
 */
class GenerateBuildManifestTest extends TestCase
{
    public function test_action_generates_build_manifest()
    {
        (new GenerateBuildManifest())->run();

        $this->assertFileExists(Hyde::path('storage/framework/cache/build-manifest.json'));
    }
}
