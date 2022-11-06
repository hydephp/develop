<?php

declare(strict_types=1);

use Hyde\Foundation\HydeKernel;

if (! function_exists('hyde')) {
    /**
     * Get the available HydeKernel instance.
     */
    function hyde(): HydeKernel
    {
        return app(HydeKernel::class);
    }
}

if (! function_exists('unslash')) {
    /**
     * Remove trailing slashes from the start and end of a string.
     */
    function unslash(string $string): string
    {
        return trim($string, '/\\');
    }
}
