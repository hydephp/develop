<?php

namespace Hyde\RealtimeCompiler\Actions;

use Hyde\RealtimeCompiler\HydeRC;
use Hyde\RealtimeCompiler\Proxy;
use Hyde\RealtimeCompiler\Server;

/**
 * Find the source file for a route
 */
class SourceFileFinder
{
    protected string $path;

    private string|null $directory;
    private string|null $basename;
    private string|null $relativePath;
    private string|null $filepath;
    private string $extension;


    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }


    public function execute(): string|null
    {
        $this->directory = $this->getType();
        $this->basename = $this->getBasename();

        $this->relativePath = $this->getRelativePath();

        Server::log('SourceFileFinder: Assuming source file is ' . $this->relativePath);

        $this->filepath = $this->getFilepath();

        if (! $this->filepath) {
            Server::log('SourceFileFinder: Could not find a source file');
            return null;
        }

        Server::log('SourceFileFinder: Found source file ' . $this->filepath);
        return $this->filepath;
    }

    private function getType()
    {
        if (str_starts_with('/posts/', $this->path)) {
            return '_posts';
        }

        if (str_starts_with('/docs/', $this->path)) {
            return '_docs';
        }

        return '_pages';

    }

    private function getBasename()
    {
        // Return everything after the last slash
        return substr($this->path, strrpos($this->path, '/') + 1);
    }

    // Guess the source file path relative to the Hyde installation
    // Assumes .md extension, but it could also be .blade.php.
    private function getRelativePath()
    {
        $components = [];

        $components[] = $this->directory;

        $components[] = $this->basename;

        return implode('/', $components) . '.md';
    }

    // Find the absolute file path
    private function getFilepath()
    {
        $filepath = $this->assembleFilepath();

        if ($this->validateExistanceOfMarkdownFile($filepath) === false && $this->validateExistanceOfBladeFile($filepath) === false) {
            return null;
        }

        return realpath($this->formatExtension($filepath));
    }


    private function assembleFilepath()
    {
        $components = [];

        $components[] = HydeRC::getHydePath();

        $components[] = trim($this->directory, '/');

        $components[] = trim($this->basename, '/');

        return implode('/', $components) . '.md';
    }

    private function validateExistanceOfMarkdownFile(string $filepath)
    {
        // Add the .md extension if it's not already there
        if (! str_ends_with($filepath, '.md')) {
            $filepath .= '.md';
        }

        if (! file_exists($filepath)) {
            Server::log('SourceFileFinder: File is not Markdown');
            return false;
        }

        $this->extension = 'md';
        Server::log('SourceFileFinder: File is Markdown');
        return true;
    }

    private function validateExistanceOfBladeFile(string $filepath)
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
            Server::log('SourceFileFinder: File is not Blade');
            return false;
        }

        $this->extension = 'blade';
        Server::log('SourceFileFinder: File is Blade');
        return true;
    }

    private function formatExtension(string $filepath)
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