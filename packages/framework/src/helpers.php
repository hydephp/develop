<?php

use Hyde\Framework\HydeKernel;
use Illuminate\Contracts\Support\Arrayable;

if (! function_exists('hyde')) {
    /**
     * Get the available HydeKernel instance.
     *
     * @return \Hyde\Framework\HydeKernel
     */
    function hyde(): HydeKernel
    {
        return app(HydeKernel::class);
    }
}

if (! function_exists('unslash')) {
    /**
     * Remove trailing slashes from the start and end of a string.
     *
     * @param  string  $string
     * @return string
     */
    function unslash(string $string): string
    {
        return trim($string, '/\\');
    }
}
