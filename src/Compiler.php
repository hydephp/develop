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

        $this->transferMediaAssets();
    }

    private function makeOutput()
    {
        $stream = $this->compile();

        // Add any transformations
        $stream = $this->transform($stream);

        return $stream;
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

    private function transferMediaAssets()
    {
        Server::log('Compiler: Transferring media assets');

        $from = HYDE_PATH . '/_site/media';
        $files = glob($from . ('/*.{png,svg,jpg,jpeg,gif,ico,css,js}'), GLOB_BRACE);
        $to = realpath('media');

        foreach ($files as $file) {
            $destination = str_replace($from, $to, $file);
            copy($file, $destination);
        }
    }

    public function getOutput(): string
    {
        return $this->output;
    }
}