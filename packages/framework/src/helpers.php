<?php

use Hyde\Framework\Hyde;

if (! function_exists('hyde')) {
    /**
     * Get the Hyde facade class.
     *
     * @return \Hyde\Framework\Hyde
     */
    function hyde(): Hyde
    {
        return new Hyde();
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
     * @param  array|\Illuminate\Support\Collection  $array  $array
     * @param  callable  $callback
     * @return array
     */
    function array_map_unique(array|\Illuminate\Support\Collection $array, callable $callback): array
    {
        if ($array instanceof \Illuminate\Support\Collection) {
            $array = $array->toArray();
        }

        return array_unique(array_map($callback, $array));
    }
}
