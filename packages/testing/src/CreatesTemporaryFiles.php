<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use function in_array;

trait CreatesTemporaryFiles
{
    protected array $fileMemory = [];

    /**
     * Create a temporary file in the project directory.
     * The TestCase will automatically remove the file when the test is completed.
     */
    protected function file(string $path, ?string $contents = null): void
    {
        if ($contents) {
            Filesystem::put($path, $contents);
        } else {
            Filesystem::touch($path);
        }

        $this->cleanUpWhenDone($path);
    }

    /**
     * Create a temporary directory in the project directory.
     * The TestCase will automatically remove the entire directory when the test is completed.
     */
    protected function directory(string $path): void
    {
        Filesystem::makeDirectory($path, recursive: true, force: true);

        $this->cleanUpWhenDone($path);
    }

    /**
     * Create a temporary Markdown+FrontMatter file in the project directory.
     */
    protected function markdown(string $path, string $contents = '', array $matter = []): void
    {
        $this->file($path, (new ConvertsArrayToFrontMatter())->execute($matter).$contents);
    }

    protected function cleanUpFilesystem(): void
    {
        if (sizeof($this->fileMemory) > 0) {
            foreach ($this->fileMemory as $file) {
                if (Filesystem::isDirectory($file)) {
                    $keep = ['_site', '_media', '_pages', '_posts', '_docs', 'app', 'config', 'storage', 'vendor', 'node_modules'];

                    if (! in_array($file, $keep)) {
                        Filesystem::deleteDirectory($file);
                    }
                } else {
                    Filesystem::unlinkIfExists($file);
                }
            }

            $this->fileMemory = [];
        }
    }

    /**
     * Mark a path to be deleted when the test is completed.
     */
    protected function cleanUpWhenDone(string $path): void
    {
        $this->fileMemory[] = $path;
    }
}
