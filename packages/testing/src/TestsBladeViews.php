<?php

declare(strict_types=1);

namespace Hyde\Testing;

trait TestsBladeViews
{
    public function view(string $view, array $data = []): TestableView
    {
        return new TestableView($view, $data);
    }
}
