<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Illuminate\View\View;
use Illuminate\Testing\TestView;

trait TestsBladeViews
{
    protected function view(string|View $view, $data = []): TestView
    {
        if ($view instanceof View) {
            return new TestView($view);
        }

        return new TestView(view($view, $data));
    }
}
