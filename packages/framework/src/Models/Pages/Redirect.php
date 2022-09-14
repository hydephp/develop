<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\HydePage;

class Redirect extends HydePage
{
    public static string $outputDirectory = '';

    public function compile(): string
    {
        return '';
    }
}
