<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Illuminate\Support\Facades\File;

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
            File::copyDirectory($directory, $directory.'-bak');
        }
    }

    final protected static function restoreDirectory(string $directory): void
    {
        if (file_exists($directory.'-bak')) {
            File::moveDirectory($directory.'-bak', $directory, true);
            File::deleteDirectory($directory.'-bak');
        }
    }

    final protected static function deleteDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            File::deleteDirectory($directory);
        }
    }

    final protected static function makeDirectory(string $directory): void
    {
        if (file_exists($directory)) {
            File::makeDirectory($directory);
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

    final protected static function stripNewlines(string $string): string
    {
        return str_replace(["\r", "\n"], '', $string);
    }

    final protected static function normalizeNewlines(string $string): string
    {
        return str_replace(["\r\n"], "\n", $string);
    }
}
