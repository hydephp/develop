<?php

namespace Hyde\RealtimeCompiler;

class Proxy
{
    protected Router $router;

    public function __construct(string $uri)
    {
        $this->router = new Router($uri);
    }

    public function serve() {
        Server::log('Proxy: Serving the request');

        if ($this->router->getSourceFile() === null) {
            return new Response('No source file could be found', 404, []);
        }

        return new Response($this->makeResponse(), 200, []);
    }

    private function makeResponse(): string
    {
        $compiler = new Compiler($this->router->getSourceFile());

        return $compiler->getOutput();
    }
}