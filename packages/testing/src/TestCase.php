<?php

namespace Hyde\Testing;

use Hyde\Facades\Features;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Illuminate\View\Component;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use ResetsApplication;
    use TestingHelpers;

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

    protected function mockRoute(?Route $route = null)
    {
        Render::share('currentRoute', $route ?? (new Route(new MarkdownPage())));
    }

    protected function mockPage(?HydePage $page = null, ?string $currentPage = null)
    {
        Render::share('page', $page ?? new MarkdownPage());
        Render::share('currentPage', $currentPage ?? 'PHPUnit');
    }

    protected function mockCurrentPage(string $currentPage)
    {
        Render::share('currentPage', $currentPage);
    }

    /**
     * Create a temporary file in the project directory.
     * The TestCase will automatically remove the file when the test is completed.
     */
    protected function file(string $path, ?string $contents = null): void
    {
        if ($contents) {
            Filesystem::put($path, $contents);
        } else {
            Filesystem::touch($path);
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
                    $keep = ['_site', '_media', '_pages', '_posts', '_docs', 'app', 'config', 'storage', 'vendor', 'node_modules'];

                    if (! in_array($file, $keep)) {
                        Filesystem::deleteDirectory($file);
                    }
                } else {
                    Filesystem::unlinkIfExists($file);
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
}
