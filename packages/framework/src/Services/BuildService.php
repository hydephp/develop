<?php

namespace Hyde\Framework\Services;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;

/**
 * Moves logic from the build command to a service.
 *
 * Handles the build loop which generates the static site.
 */
class BuildService
{
    use InteractsWithIO;

    public function __construct(OutputStyle $output)
    {
        $this->output = $output;
    }
}
