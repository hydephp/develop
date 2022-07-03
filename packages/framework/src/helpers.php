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
     * @param string $string
     * @return string
     */
    function unslash(string $string): string
    {
        return trim($string, '/\\');
    }
}