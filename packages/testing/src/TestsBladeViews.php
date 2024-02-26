<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Illuminate\View\View;
use Hyde\Testing\Support\TestView;

/**
 * Provides a more fluent way to test Blade views.
 */
trait TestsBladeViews
{
    /**
     * Test a Blade view.
     */
    protected function test(string|View $view, $data = []): TestView
    {
        $data = array_merge($this->testViewData(), $data);

        if ($view instanceof View) {
            return new TestView($view->with($data));
        }

        return new TestView(view($view, $data));
    }

    /**
     * Define any view data to pass to all views in the test.
     */
    protected function testViewData(): array
    {
        return [];
    }
}
