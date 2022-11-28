<?php

use Illuminate\Support\Facades\File;

if (! function_exists('unlinkIfExists')) {
    function unlinkIfExists(string $filepath): void
    {
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}

if (! function_exists('backupDirectory')) {
    function backupDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            File::copyDirectory($directory, $directory.'-bak', true);
        }
    }
}

if (! function_exists('restoreDirectory')) {
    function restoreDirectory(string $directory): void
    {
        if (file_exists($directory.'-bak')) {
            File::moveDirectory($directory.'-bak', $directory, true);
            File::deleteDirectory($directory.'-bak');
        }
    }
}

if (! function_exists('deleteDirectory')) {
    function deleteDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            File::deleteDirectory($directory);
        }
    }
}

if (! function_exists('makeDirectory')) {
    function makeDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            File::makeDirectory($directory);
        }
    }
}

if (! function_exists('unlinkUnlessDefault')) {
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
    function strip_newlines(string $string, bool $keepUnixEndings = false): string
    {
        if ($keepUnixEndings) {
            return str_replace("\r", '', $string);
        }

        return str_replace(["\r", "\n"], '', $string);
    }
}

if (! function_exists('strip_indentation')) {
    function strip_indentation(string $string, bool $indentUsingSpaces = true, int $indentationLength = 4): string
    {
        $indentation = $indentUsingSpaces ? str_repeat(' ', $indentationLength) : "\t";

        return str_replace($indentation, '', $string);
    }
}

if (! function_exists('strip_newlines_and_indentation')) {
    function strip_newlines_and_indentation(string $string, bool $indentUsingSpaces = true, int $indentationLength = 4): string
    {
        return strip_newlines(strip_indentation($string, $indentUsingSpaces, $indentationLength));
    }
}
