<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Actions\PostBuildTasks\GenerateBuildManifest;
use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateBuildManifest
 */
class GenerateBuildManifestTest extends TestCase
{
    public function test_action_generates_build_manifest()
    {
        (new GenerateBuildManifest())->run();

        $this->assertFileExists(Hyde::path('storage/framework/cache/build-manifest.json'));

        $manifest = json_decode(file_get_contents(Hyde::path('storage/framework/cache/build-manifest.json')), true);

        $this->assertIsArray($manifest);
        $this->assertCount(2, $manifest);

        $this->assertArrayHasKey('page', $manifest[0]);
        $this->assertArrayHasKey('source_hash', $manifest[0]);
        $this->assertArrayHasKey('output_hash', $manifest[0]);

        $this->assertStringContainsString('_pages/404.blade.php', $manifest[0]['page']);
        $this->assertStringContainsString('_pages/index.blade.php', $manifest[1]['page']);
    }
}
