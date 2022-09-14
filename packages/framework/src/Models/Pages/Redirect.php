<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\HydePage;

class Redirect extends HydePage
{
    public static string $outputDirectory = '';

    public string $path;
    public string $destination;

    public function __construct(string $path, string $destination)
    {
        parent::__construct();

        $this->path = $path;
        $this->destination = $destination;
    }

    public static function make(string $path, string $destination): static
    {
        return new static($path, $destination);
    }

    public function compile(): string
    {
        return '';
    }
}
