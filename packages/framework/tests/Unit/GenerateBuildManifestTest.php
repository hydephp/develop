<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Actions\PostBuildTasks\GenerateBuildManifest;
use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;

use function Hyde\unixsum_file;

/**
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateBuildManifest
 */
class GenerateBuildManifestTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    public function testActionGeneratesBuildManifest()
    {
        (new GenerateBuildManifest())->handle();

        $this->assertFileExists(Hyde::path('app/storage/framework/cache/build-manifest.json'));

        $manifest = json_decode(file_get_contents(Hyde::path('app/storage/framework/cache/build-manifest.json')), true);

        $this->assertIsArray($manifest);

        $this->assertCount(2, $manifest);
        $this->assertCount(2, $manifest['pages']);

        $this->assertSame([404, 'index'], array_keys($manifest['pages']));

        $this->assertArrayHasKey('source_path', $manifest['pages'][404]);
        $this->assertArrayHasKey('output_path', $manifest['pages'][404]);
        $this->assertArrayHasKey('source_hash', $manifest['pages'][404]);
        $this->assertArrayHasKey('output_hash', $manifest['pages'][404]);

        $this->assertEquals('_pages/404.blade.php', $manifest['pages'][404]['source_path']);
        $this->assertEquals('_pages/index.blade.php', $manifest['pages']['index']['source_path']);

        $this->assertEquals('404.html', $manifest['pages'][404]['output_path']);
        $this->assertEquals('index.html', $manifest['pages']['index']['output_path']);

        $this->assertEquals(unixsum_file(Hyde::path('_pages/404.blade.php')), $manifest['pages'][404]['source_hash']);
        $this->assertNull($manifest['pages'][404]['output_hash']);
    }
}
