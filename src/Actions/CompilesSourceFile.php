<?php

namespace Hyde\RealtimeCompiler\Actions;

use Hyde\RealtimeCompiler\Server;

/**
 * Hook into Hyde to compile a source file.
 */
class CompilesSourceFile
{
    /**
     * The source file to compile.
     * @var string
     */
    private string $path;

    /**
     * Construct a new Action instance.
     */
    public function __construct(string $path)
    {
        // Remove everything before the first underscore
        $this->path = substr($path, strpos($path, '_'));
    }

    /**
     * Trigger the compiler and return the compiled HTML string.
     * @return string
     */
    public function execute(): bool|string
    {
        Server::log('Compiler: Building page...');
        $output =  shell_exec('php '.HYDE_PATH.'/hyde rebuild '.$this->path);
        Server::log('Compiler: ' . $output);

        return file_get_contents(HYDE_PATH . '/_site/'. $this->formatPathname());
    }

    /**
     * Convert the source path string to the output path string.
     * @return string
     */
    private function formatPathname(): string
    {
        $filename = $this->path;

        $filename = str_replace('_pages', '', $filename);
        $filename = str_replace('_', '', $filename);
        $filename = str_replace('.blade.php', '.html', $filename);
        $filename = str_replace('.md', '.html', $filename);

        return $filename;
    }
}