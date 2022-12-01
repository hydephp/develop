<?php

namespace Hyde\Testing;

use Hyde\Facades\Features;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Models\Route;
use Illuminate\View\Component;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;
use function strip_newlines;

require_once __DIR__.'/helpers.php';

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use ResetsApplication;

    protected static bool $booted = false;

    protected array $fileMemory = [];

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$booted) {
            $this->resetApplication();

            static::$booted = true;
        }
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
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

    protected function assertEqualsIgnoringLineEndingType(string $expected, string $actual): void
    {
        $this->assertEquals(
            strip_newlines($expected, true),
            strip_newlines($actual, true),
        );
    }

    /** @internal */
    protected function mockRoute(?Route $route = null)
    {
        view()->share('currentRoute', $route ?? (new Route(new MarkdownPage())));
    }

    /** @internal */
    protected function mockPage(?HydePage $page = null, ?string $currentPage = null)
    {
        view()->share('page', $page ?? new MarkdownPage());
        view()->share('currentPage', $currentPage ?? 'PHPUnit');
    }

    /** @internal */
    protected function mockCurrentPage(string $currentPage)
    {
        view()->share('currentPage', $currentPage);
    }

    /**
     * Create a temporary file in the project directory.
     * The TestCase will automatically remove the file when the test is completed.
     */
    protected function file(string $path, ?string $contents = null): void
    {
        if ($contents) {
            file_put_contents(Hyde::path($path), $contents);
        } else {
            Hyde::touch($path);
        }

        $this->cleanUpWhenDone($path);
    }

    /**
     * Create a temporary directory in the project directory.
     * The TestCase will automatically remove the entire directory when the test is completed.
     */
    protected function directory(string $path): void
    {
        Filesystem::makeDirectory($path, recursive: true, force: true);

        $this->cleanUpWhenDone($path);
    }

    /**
     * Create a temporary Markdown+FrontMatter file in the project directory.
     */
    protected function markdown(string $path, string $contents = '', array $matter = []): void
    {
        $this->file($path, (new ConvertsArrayToFrontMatter())->execute($matter).$contents);
    }

    protected function cleanUpFilesystem(): void
    {
        if (sizeof($this->fileMemory) > 0) {
            foreach ($this->fileMemory as $file) {
                if (Filesystem::isDirectory($file)) {
                    $dontDelete = ['_site', '_media', '_pages', '_posts', '_docs', 'app', 'config', 'storage', 'vendor', 'node_modules'];

                    if (! in_array($file, $dontDelete)) {
                        Filesystem::deleteDirectory($file);
                    }
                } else {
                    Filesystem::unlink($file);
                }
            }
            $this->fileMemory = [];
        }
    }

    /**
     * Mark a path to be deleted when the test is completed.
     */
    protected function cleanUpWhenDone(string $path): void
    {
        $this->fileMemory[] = $path;
    }

    protected function setupTestPublication(string $pubName = 'test-publication')
    {
        copy(Hyde::path('tests/fixtures/test-publication-schema.json'), Hyde::path("$pubName/schema.json"));
    }
}
