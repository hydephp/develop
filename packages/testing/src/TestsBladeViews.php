<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Illuminate\Testing\TestView;

trait TestsBladeViews
{
    protected function view(string $view, $data = []): TestView
    {
        return new TestView(view($view, $data));
    }
}
