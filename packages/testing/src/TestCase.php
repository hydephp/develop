<?php

declare(strict_types=1);

namespace Hyde\Testing;

use function file_get_contents;
use Hyde\Facades\Features;
use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use function Hyde\normalize_newlines;
use Illuminate\View\Component;
use JetBrains\PhpStorm\Deprecated;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use ResetsApplication;
    use CreatesTemporaryFiles;
    use InteractsWithPages;

    protected static bool $booted = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$booted) {
            $this->resetApplication();

            static::$booted = true;
        }
    }

    protected function tearDown(): void
    {
        $this->cleanUpFilesystem();

        if (method_exists(\Illuminate\View\Component::class, 'flushCache')) {
            /** Until https://github.com/laravel/framework/pull/44648 makes its way into Laravel Zero, we need to clear the cache ourselves */
            Component::flushCache();
            Component::forgetComponentsResolver();
            Component::forgetFactory();
        }

        Features::clearMockedInstances();

        parent::tearDown();
    }

    /** @deprecated Will be removed as it's no longer part of the main package. */
    #[Deprecated(reason: "Will be removed as it's no longer part of the main package. In most cases you can use the following replacement:", replacement: "(new PublicationType('Test Publication'))->save();")]
    protected function setupTestPublication(string $directory = 'test-publication')
    {
        Filesystem::copy('tests/fixtures/test-publication-schema.json', "$directory/schema.json");
    }

    protected function assertFileEqualsString(string $string, string $path, bool $strict = false): void
    {
        if ($strict) {
            $this->assertSame($string, file_get_contents(Hyde::path($path)));
        } else {
            $this->assertEquals(normalize_newlines($string), normalize_newlines(file_get_contents(Hyde::path($path))));
        }
    }
}
