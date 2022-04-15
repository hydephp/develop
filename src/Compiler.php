<?php

namespace Hyde\RealtimeCompiler;

use Hyde\RealtimeCompiler\Actions\CompilesSourceFile;

class Compiler
{
    public string $path;
    public string $output;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->output = $this->makeOutput();
    }

    private function makeOutput(): string
    {
        $stream = $this->compile();

        // Add any transformations
        return $this->transform($stream);
    }

    private function compile(): string
    {
        return (new CompilesSourceFile($this->path))->execute();
    }

    private function transform(string $stream): string
    {
        return sprintf("%s<!-- Hyde Realtime Compiler proxied, compiled, and served this request in %sms -->",
            $stream,
            HydeRC::getExecutionTime());
    }

    public function getOutput(): string
    {
        return $this->output;
    }
}