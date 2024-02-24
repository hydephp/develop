<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Illuminate\View\View;
use Illuminate\Testing\TestView;

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
        if ($view instanceof View) {
            return new TestView($view);
        }

        return new TestView(view($view, $data));
    }
}
