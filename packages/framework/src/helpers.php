<?php

declare(strict_types=1);

namespace {
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
}

namespace Hyde {
    use Hyde\Foundation\HydeKernel;

    if (! function_exists('\Hyde\hyde')) {
        /**
         * Get the available HydeKernel instance.
         */
        function hyde(): HydeKernel
        {
            return app(HydeKernel::class);
        }
    }

    if (! function_exists('\Hyde\unslash')) {
        /**
         * Remove trailing slashes from the start and end of a string.
         */
        function unslash(string $string): string
        {
            return trim($string, '/\\');
        }
    }

    if (! function_exists('\Hyde\make_title')) {
        function make_title(string $value): string
        {
            return Hyde::makeTitle($value);
        }
    }

    if (! function_exists('\Hyde\normalize_newlines')) {
        function normalize_newlines(string $string): string
        {
            return Hyde::normalizeNewlines($string);
        }
    }

    if (! function_exists('\Hyde\strip_newlines')) {
        function strip_newlines(string $string): string
        {
            return Hyde::stripNewlines($string);
        }
    }

    if (! function_exists('\Hyde\trim_slashes')) {
        function trim_slashes(string $string): string
        {
            return Hyde::trimSlashes($string);
        }
    }
}
