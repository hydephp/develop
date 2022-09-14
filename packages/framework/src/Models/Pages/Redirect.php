<?php

namespace Hyde\Framework\Models\Pages;

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
}
