<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Hyde;
use Illuminate\Support\Facades\File;
use function copy;

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

    protected function withoutDefaultPages(): void
    {
        Hyde::unlink('_pages/404.blade.php');
        Hyde::unlink('_pages/index.blade.php');
    }

    protected function restoreDefaultPages(): void
    {
        copy(Hyde::vendorPath('resources/views/homepages/welcome.blade.php'), Hyde::path('_pages/index.blade.php'));
        copy(Hyde::vendorPath('resources/views/pages/404.blade.php'), Hyde::path('_pages/404.blade.php'));
    }
}
