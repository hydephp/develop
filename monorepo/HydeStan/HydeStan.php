<?php

declare(strict_types=1);

/**
 * @internal
 */
class HydeStan
{
    protected array $errors = [];
    protected array $files;
    protected Console $console;

    public function __construct()
    {
        $this->console = new Console();

        $this->console->info('HydeStan is running!');
    }

    public function __destruct()
    {
        $this->console->info('HydeStan has finished.');
    }

    public function run(): void
    {
        $this->files = $this->getFiles();

        foreach ($this->files as $file) {
            $this->console->debug('Analysing file: ' . $file);

            $this->analyseFile($file, $this->getFileContents($file));
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function getFiles(): array
    {
        $files = [];

        $directory = new RecursiveDirectoryIterator(BASE_PATH . '/src');
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

        foreach ($regex as $file) {
            $files[] = substr($file[0], strlen(BASE_PATH) + 1);
        }

        return $files;
    }

    private function analyseFile(string $file, string $getFileContents): void
    {
        // TODO
    }

    private function getFileContents(string $file): string
    {
        return file_get_contents(BASE_PATH . '/' . $file);
    }
}
