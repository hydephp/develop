<?php

namespace Hyde\RealtimeCompiler;

use Hyde\RealtimeCompiler\Actions\SourceFileFinder;

/**
 * Find the correct source file for the request route.
 */
class Router
{
    public string $uri;
    private $sourceFile;

    /**
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $this->uri = $uri;

        $this->handle();
    }

    private function handle()
    {
        Server::log('Router: Attempting to find source file to route ' . $this->uri);

        $sourceFile = static::findSourceFile($this->uri);

        if ($sourceFile === null) {
            Server::log('Router: No source file found for ' . $this->uri);
        } else {
            Server::log('Router: Found source file ' . $sourceFile);
        }

        return $this->sourceFile;
    }


    /**
     * Attempt a reverse lookup of the Hyde source file for the given URI.
     * @return string|null The source file path, or null if not found.
     */
    public static function findSourceFile(string $uri)
    {
        return (new SourceFileFinder($uri))->execute();
    }

    public function getSourceFile()
    {
        return $this->sourceFile;
    }
}