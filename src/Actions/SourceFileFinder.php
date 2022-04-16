<?php

namespace Hyde\RealtimeCompiler\Actions;

use Hyde\RealtimeCompiler\Server;

/**
 * Find the source file for a compiled HTML file.
 */
class SourceFileFinder
{
    protected string $path;

    private string|null $directory;
    private string|null $basename;
    private string $extension;

    /**
     * @param string $path to the compiled HTML file.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function execute(): string|null
    {
        $this->directory = $this->getType();
        $this->basename = $this->getBasename();

        $relativePath = $this->getRelativePath();

        Server::log('SourceFileFinder: Assuming source file is ' . $relativePath, true);

        $filepath = $this->getFilepath();

        if (!$filepath) {
            Server::log('SourceFileFinder: Could not find a source file');
            return null;
        }

        Server::log('SourceFileFinder: Found source file ' . $filepath, true);
        return $filepath;
    }

    /**
     * Determine the type of the compiled HTML file
     * so we know what directory to look for the source in.
     *
     * @return string
     */
    private function getType(): string
    {
        if (str_starts_with($this->path, '/posts/')) {
            return '_posts';
        }

        if (str_starts_with($this->path, '/docs/')) {
            return '_docs';
        }

        return '_pages';

    }

    private function getBasename(): string
    {
        // Return everything after the last slash
        return substr($this->path, strrpos($this->path, '/') + 1);
    }

    // Guess the source file path relative to the Hyde installation
    // Assumes .md extension, but it could also be .blade.php.
    private function getRelativePath(): string
    {
        $components = [];

        $components[] = $this->directory;

        $components[] = $this->basename;

        return implode('/', $components) . '.md';
    }

    // Find the absolute file path
    private function getFilepath(): bool|string|null
    {
        $filepath = $this->assembleFilepath();

        if ($this->validateExistenceOfMarkdownFile($filepath) === false && $this->validateExistenceOfBladeFile($filepath) === false) {
            return null;
        }

        return realpath($this->formatExtension($filepath));
    }


    private function assembleFilepath(): string
    {
        $components = [];

        $components[] = HYDE_PATH;

        $components[] = trim($this->directory, '/');

        $components[] = trim($this->basename, '/');

        return implode('/', $components) . '.md';
    }

    private function validateExistenceOfMarkdownFile(string $filepath): bool
    {
        // Add the .md extension if it's not already there
        if (! str_ends_with($filepath, '.md')) {
            $filepath .= '.md';
        }

        if (! file_exists($filepath)) {
            Server::log('SourceFileFinder: File is not Markdown', true);
            return false;
        }

        $this->extension = 'md';
        Server::log('SourceFileFinder: File is Markdown', true);
        return true;
    }

    private function validateExistenceOfBladeFile(string $filepath): bool
    {
        // If the file ends in .md, remove it
        if (str_ends_with($filepath, '.md')) {
            $filepath = substr($filepath, 0, -3);
        }

        // Add the .blade.php extension if it's not already there
        if (! str_ends_with($filepath, '.blade.php')) {
            $filepath .= '.blade.php';
        }

        if (! file_exists($filepath)) {
            Server::log('SourceFileFinder: File is not Blade', true);
            return false;
        }

        $this->extension = 'blade';
        Server::log('SourceFileFinder: File is Blade', true);
        return true;
    }

    private function formatExtension(string $filepath): string
    {
        if (str_ends_with($filepath, '.md')) {
            $filepath = substr($filepath, 0, -3);
        }

        if (str_ends_with($filepath, '.blade.php')) {
            $filepath = substr($filepath, 0, -10);
        }

        if ($this->extension === 'md') {
            return $filepath . '.md';
        }

        return $filepath . '.blade.php';
    }
}