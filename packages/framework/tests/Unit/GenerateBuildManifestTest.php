<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Actions\PostBuildTasks\GenerateBuildManifest;
use Hyde\Framework\Services\ChecksumService;
use Hyde\Hyde;
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
        $this->assertCount(2, $manifest['pages']);

        $this->assertArrayHasKey('source_path', $manifest['pages'][0]);
        $this->assertArrayHasKey('output_path', $manifest['pages'][0]);
        $this->assertArrayHasKey('source_hash', $manifest['pages'][0]);
        $this->assertArrayHasKey('output_hash', $manifest['pages'][0]);

        $this->assertEquals('_pages/404.blade.php', $manifest['pages'][0]['source_path']);
        $this->assertEquals('_pages/index.blade.php', $manifest['pages'][1]['source_path']);

        $this->assertEquals('404.html', $manifest['pages'][0]['output_path']);
        $this->assertEquals('index.html', $manifest['pages'][1]['output_path']);

        $this->assertEquals(ChecksumService::unixsumFile(Hyde::path('_pages/404.blade.php')), $manifest['pages'][0]['source_hash']);
        $this->assertNull($manifest['pages'][0]['output_hash']);
    }
}
