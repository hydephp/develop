<?php

namespace Hyde\RealtimeCompiler;

class Proxy
{
    protected Router $router;

    public function __construct(string $uri)
    {
        $this->router = new Router($uri);
    }

    // Create and serve a new response
    public function serve(): Response
    {
        Server::log('Proxy: Serving the request');

        if ($this->router->getSourceFile() === null) {
            return new Response('No source file could be found', 404, []);
        }

        return new Response($this->makeResponse(), 200, []);
    }

    // Create the response body by compiling the source file and returning the output stream as a string
    private function makeResponse(): string
    {
        $compiler = new Compiler($this->router->getSourceFile());

        return $compiler->getOutput();
    }
}