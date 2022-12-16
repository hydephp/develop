<?php

namespace Hyde\Testing;

trait TestingHelpers
{
    public static function unlinkIfExists(string $filepath): void
    {
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    public static function backupDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            \Illuminate\Support\Facades\File::copyDirectory($directory, $directory . '-bak', true);
        }
    }

    public static function restoreDirectory(string $directory): void
    {
        if (file_exists($directory . '-bak')) {
            \Illuminate\Support\Facades\File::moveDirectory($directory . '-bak', $directory, true);
            \Illuminate\Support\Facades\File::deleteDirectory($directory . '-bak');
        }
    }

    public static function deleteDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            \Illuminate\Support\Facades\File::deleteDirectory($directory);
        }
    }

    public static function makeDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            \Illuminate\Support\Facades\File::makeDirectory($directory);
        }
    }

    public static function unlinkUnlessDefault(string $filepath): void
    {
        $protected = [
            'app.css',
            'index.blade.php',
            '404.blade.php',
            '.gitkeep',
        ];

        if (!in_array(basename($filepath), $protected)) {
            unlink($filepath);
        }
    }

    public static function strip_newlines(string $string, bool $keepUnixEndings = false): string
    {
        if ($keepUnixEndings) {
            return str_replace("\r", '', $string);
        }

        return str_replace(["\r", "\n"], '', $string);
    }


    public static function strip_indentation(string $string, bool $indentUsingSpaces = true, int $indentationLength = 4): string
    {
        $indentation = $indentUsingSpaces ? str_repeat(' ', $indentationLength) : "\t";

        return str_replace($indentation, '', $string);
    }

    public static function strip_newlines_and_indentation(string $string, bool $indentUsingSpaces = true, int $indentationLength = 4): string
    {
        return self::strip_newlines(self::strip_indentation($string, $indentUsingSpaces, $indentationLength));
    }
}
