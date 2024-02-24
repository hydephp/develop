<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Testing\Support\TestableView;

trait TestsBladeViews
{
    public function view(string $view, array $data = []): TestableView
    {
        return new TestableView($view, $data);
    }
}
