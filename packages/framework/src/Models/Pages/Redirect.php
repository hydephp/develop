<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Hyde;

class Redirect
{
    public string $path;
    public string $destination;

    public function __construct(string $path, string $destination)
    {
        $this->path = $path;
        $this->destination = $destination;
    }

    public static function make(string $path, string $destination): static
    {
        return new static($path, $destination);
    }

    public function render(): string
    {
        return '';
    }

    public function store(): static
    {
        file_put_contents(Hyde::sitePath($this->path), $this->render());

        return $this;
    }
}
