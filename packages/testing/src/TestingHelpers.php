<?php

namespace Hyde\Testing;

trait TestingHelpers
{
    final protected static function unlinkIfExists(string $filepath): void
    {
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    final protected static function backupDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            \Illuminate\Support\Facades\File::copyDirectory($directory, $directory.'-bak', true);
        }
    }

    final protected static function restoreDirectory(string $directory): void
    {
        if (file_exists($directory.'-bak')) {
            \Illuminate\Support\Facades\File::moveDirectory($directory.'-bak', $directory, true);
            \Illuminate\Support\Facades\File::deleteDirectory($directory.'-bak');
        }
    }

    final protected static function deleteDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            \Illuminate\Support\Facades\File::deleteDirectory($directory);
        }
    }

    final protected static function makeDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            \Illuminate\Support\Facades\File::makeDirectory($directory);
        }
    }

    final protected static function unlinkUnlessDefault(string $filepath): void
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

    final protected static function stripNewlines(string $string, bool $keepUnixEndings = false): string
    {
        if ($keepUnixEndings) {
            return str_replace("\r", '', $string);
        }

        return str_replace(["\r", "\n"], '', $string);
    }

    final protected static function stripIndentation(string $string, bool $indentUsingSpaces = true, int $indentationLength = 4): string
    {
        $indentation = $indentUsingSpaces ? str_repeat(' ', $indentationLength) : "\t";

        return str_replace($indentation, '', $string);
    }

    final protected static function stripNewlinesAndIndentation(string $string, bool $indentUsingSpaces = true, int $indentationLength = 4): string
    {
        return self::stripNewlines(self::stripIndentation($string, $indentUsingSpaces, $indentationLength));
    }
}
