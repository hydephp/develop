<?php

namespace Hyde\RealtimeCompiler;

use Hyde\RealtimeCompiler\Actions\SourceFileFinder;

/**
 * Find the correct source file for the request URI route.
 */
class Router
{
    /**
     * The requested URI route.
     */
    public string $uri;

    /**
     * The path of the source file, if found, else null.
     */
    private string|null $sourceFile;

    /**
     * @param string $uri to reverse-match the source file.
     */
    public function __construct(string $uri)
    {
        $this->uri = $uri;

        $this->sourceFile = $this->handle();
    }

    /**
     * Find the source file for the requested URI route.
     * @return string|null The path of the source file, if found, else null.
     */
    private function handle(): ?string
    {
        Server::log('Router: Attempting to find source file to route ' . $this->uri);

        $sourceFile = static::findSourceFile($this->uri);

        if ($sourceFile === null) {
            Server::log('Router: No source file found for ' . $this->uri);
        } else {
            Server::log('Router: Found source file ' . $sourceFile);
        }

        return $sourceFile;
    }

    /**
     * Attempt a reverse lookup of the Hyde source file for the given URI.
     * @return string|null The source file path, or null if not found.
     */
    public static function findSourceFile(string $uri): ?string
    {
        return (new SourceFileFinder($uri))->execute();
    }

    /**
     * Get the path of the source file, if found, else null. 
     */
    public function getSourceFile(): ?string
    {
        return $this->sourceFile;
    }
}