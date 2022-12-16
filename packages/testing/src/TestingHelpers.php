<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Illuminate\Support\Facades\File;

trait TestingHelpers
{
    final protected static function backupDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            File::copyDirectory($directory, $directory.'-bak');
        }
    }

    final protected static function restoreDirectory(string $directory): void
    {
        if (is_dir($directory.'-bak')) {
            File::moveDirectory($directory.'-bak', $directory, true);
            File::deleteDirectory($directory.'-bak');
        }
    }

    final protected static function deleteDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            File::deleteDirectory($directory);
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

    protected function assertEqualsIgnoringLineEndingType(string $expected, string $actual): void
    {
        $this->assertEquals(
            $this->normalizeNewlines($expected),
            $this->normalizeNewlines($actual),
        );
    }
}
