<?php

use Hyde\Framework\Contracts\HydeKernelContract;
use Illuminate\Contracts\Support\Arrayable;

if (! function_exists('hyde')) {
    /**
     * Get the available HydeKernel instance.
     *
     * @return \Hyde\Framework\Contracts\HydeKernelContract
     */
    function hyde(): HydeKernelContract
    {
        return app(HydeKernelContract::class);
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

if (! function_exists('array_map_unique')) {
    /**
     * Map a callback over an array and remove duplicates.
     *
     * Important! The callback and the array parameter positions
     * are reversed compared to the PHP function.
     *
     * Unlike array_unique, keys are reset.
     *
     * @param  array|\Illuminate\Contracts\Support\Arrayable  $array
     * @param  callable  $callback
     * @return array
     */
    function array_map_unique(array|Arrayable $array, callable $callback): array
    {
        if ($array instanceof Arrayable) {
            $array = $array->toArray();
        }

        return array_values(array_unique(array_map($callback, $array)));
    }
}
