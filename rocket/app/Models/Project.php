<?php

namespace App\Models;

/**
 * Abstraction for the current Hyde project.
 */
class Project
{
    public string $path;
    public string $name;

    public function __construct()
    {
        $this->path = $this->getPathOrFail();
        $this->name = ucwords(basename($this->path));
    }

    protected function getPathOrFail(): string
    {
        $path = realpath(getcwd() . '/../../');
        if (!is_dir($path)) {
            throw new \Exception("Not a directory.");
        }
        if (!is_file($path . '/hyde')) {
            throw new \Exception("Not a Hyde project.");
        }
        return $path;
    }

    public static function get(): static
    {
        return new static();
    }
}
