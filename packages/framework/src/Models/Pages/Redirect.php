<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\HydePage;

class Redirect extends HydePage
{
    public static string $outputDirectory = '';

    public string $destination;

    public function __construct(string $destination)
    {
        parent::__construct();

        $this->destination = $destination;
    }

    public function compile(): string
    {
        return '';
    }
}
