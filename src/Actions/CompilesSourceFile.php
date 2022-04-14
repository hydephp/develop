<?php

namespace Hyde\RealtimeCompiler\Actions;

use Hyde\Framework\Services\RebuildService;
use Hyde\RealtimeCompiler\Server;

/**
 * Hook into Hyde to compile a source file.
 */
class CompilesSourceFile
{
    private string $path;

    public function __construct(string $path)
    {
        // Remove everything before the first underscore
        $this->path = substr($path, strpos($path, '_'));
    }

    public function execute()
    {
        $output =  shell_exec('php ../../hyde rebuild '.$this->path);
        Server::log('Compiler: ' . $output);

        $filename = str_replace('.blade.php', '.html', basename($this->path));
        $filename = str_replace('.md', '.html', $filename);

        return file_get_contents('../../_site/'. $filename);
    }
}