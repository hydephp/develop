<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;

use function is_dir;
use function dirname;
use function in_array;

trait CreatesTemporaryFiles
{
    protected array $fileMemory = [];

    /**
     * Create a temporary file in the project directory.
     *
     * The test case will automatically remove the file when the test is completed.
     */
    protected function file(string $path, ?string $contents = null): void
    {
        if (! is_dir(dirname($path))) {
            $this->directory(dirname($path));
        }

        if ($contents) {
            Filesystem::put($path, $contents);
        } else {
            Filesystem::touch($path);
        }

        $this->cleanUpWhenDone($path);
    }

    /**
     * List of filenames, or map of filenames to contents, of temporary files to create in the project directory.
     *
     * The test case will automatically remove the files when the test is completed.
     */
    protected function files(array $files): void
    {
        foreach ($files as $path => $contents) {
            if (is_int($path)) {
                $path = $contents;
                $contents = null;
            }

            $this->file($path, $contents);
        }
    }

    /**
     * Create a temporary directory in the project directory.
     *
     * The test case will automatically remove the entire directory when the test is completed.
     */
    protected function directory(string $path): void
    {
        Filesystem::makeDirectory($path, recursive: true, force: true);

        $this->cleanUpWhenDone($path);
    }

    /**
     * Create a temporary Markdown file with front matter in the project directory.
     */
    protected function markdown(string $path, string $contents = '', array $matter = []): void
    {
        $this->file($path, (new ConvertsArrayToFrontMatter())->execute($matter).$contents);
    }

    /**
     * Clean up the filesystem after the test has completed.
     */
    protected function cleanUpFilesystem(): void
    {
        if (sizeof($this->fileMemory) > 0) {
            foreach ($this->fileMemory as $file) {
                if (Filesystem::isDirectory($file)) {
                    if (! in_array($file, ['_site', '_media', '_pages', '_posts', '_docs', 'app', 'config', 'storage', 'vendor', 'node_modules'])) {
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
