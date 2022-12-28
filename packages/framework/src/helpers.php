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
    use Illuminate\Support\HtmlString;

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

    if (! function_exists('\Hyde\makeTitle')) {
        function makeTitle(string $value): string
        {
            return Hyde::makeTitle($value);
        }
    }

    if (! function_exists('\Hyde\normalizeNewlines')) {
        function normalizeNewlines(string $string): string
        {
            return Hyde::normalizeNewlines($string);
        }
    }

    if (! function_exists('\Hyde\stripNewlines')) {
        function stripNewlines(string $string): string
        {
            return Hyde::stripNewlines($string);
        }
    }

    if (! function_exists('\Hyde\trimSlashes')) {
        function trimSlashes(string $string): string
        {
            return Hyde::trimSlashes($string);
        }
    }

    if (! function_exists('\Hyde\markdown')) {
        function markdown(string $text, bool $normalizeIndentation = false): HtmlString
        {
            return Hyde::markdown($text, $normalizeIndentation);
        }
    }
}
