<?php

namespace Hyde\Testing;

use Hyde\Facades\Features;
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
    use CreatesTemporaryFiles;

    protected static bool $booted = false;

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
}
