<?php /** @deprecated */

use Illuminate\Support\Facades\File;

if (! function_exists('unlinkIfExists')) {
    /** @deprecated */
    function unlinkIfExists(string $filepath): void
    {
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}

if (! function_exists('backupDirectory')) {
    /** @deprecated */
    function backupDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            File::copyDirectory($directory, $directory.'-bak', true);
        }
    }
}

if (! function_exists('restoreDirectory')) {
    /** @deprecated */
    function restoreDirectory(string $directory): void
    {
        if (file_exists($directory.'-bak')) {
            File::moveDirectory($directory.'-bak', $directory, true);
            File::deleteDirectory($directory.'-bak');
        }
    }
}

if (! function_exists('deleteDirectory')) {
    /** @deprecated */
    function deleteDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            File::deleteDirectory($directory);
        }
    }
}

if (! function_exists('makeDirectory')) {
    /** @deprecated */
    function makeDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            File::makeDirectory($directory);
        }
    }
}

if (! function_exists('unlinkUnlessDefault')) {
    /** @deprecated */
    function unlinkUnlessDefault(string $filepath): void
    {
        $protected = [
            'app.css',
            'index.blade.php',
            '404.blade.php',
            '.gitkeep',
        ];

        if (! in_array(basename($filepath), $protected)) {
            unlink($filepath);
        }
    }
}

if (! function_exists('strip_newlines')) {
    /** @deprecated */
    function strip_newlines(string $string, bool $keepUnixEndings = false): string
    {
        if ($keepUnixEndings) {
            return str_replace("\r", '', $string);
        }

        return str_replace(["\r", "\n"], '', $string);
    }
}

if (! function_exists('strip_indentation')) {
    /** @deprecated */
    function strip_indentation(string $string, bool $indentUsingSpaces = true, int $indentationLength = 4): string
    {
        $indentation = $indentUsingSpaces ? str_repeat(' ', $indentationLength) : "\t";

        return str_replace($indentation, '', $string);
    }
}

if (! function_exists('strip_newlines_and_indentation')) {
    /** @deprecated */
    function strip_newlines_and_indentation(string $string, bool $indentUsingSpaces = true, int $indentationLength = 4): string
    {
        return strip_newlines(strip_indentation($string, $indentUsingSpaces, $indentationLength));
    }
}
