<?php

namespace App\Models;

/**
 * Abstraction for the current Hyde project.
 */
class Project
{
    protected static Project $instance;
    protected Hyde $hyde;

    public string $path;
    public string $name;

    protected function __construct()
    {
        $this->path = $this->getPathOrFail();
        $this->name = ucwords(basename($this->path));
        $this->hyde = new Hyde($this->path);
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

    public function hyde(): Hyde
    {
        return $this->hyde;
    }

    public static function get(): static
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
