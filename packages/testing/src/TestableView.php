<?php

declare(strict_types=1);

namespace Hyde\Testing\Support;

class TestableView
{
    protected string $view;
    protected array $data = [];

    public function __construct(string $view, array $data = [])
    {
        $this->data = $data;
        $this->view = $view;
    }
}
