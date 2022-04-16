<?php

namespace Hyde\RealtimeCompiler;

use Hyde\RealtimeCompiler\Actions\CompilesSourceFile;

/**
 * Compiles a Hyde source file into static HTML and return the result.
 */
class Compiler
{
    /**
     * The source file to compile.
     * @var string
     */
    public string $path;

    /**
     * The compiled HTML stream.
     * @var string
     */
    public string $output;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->output = $this->makeOutput();
    }

    /**
     * Trigger the compiler and transformations and return the result.
     *
     * @return string
     */
    private function makeOutput(): string
    {
        $stream = $this->compile();

        // Add any transformations
        return $this->transform($stream);
    }

    /**
     * Trigger the compiler and return the result.
     * 
     * @return string
     */
    private function compile(): string
    {
        return (new CompilesSourceFile($this->path))->execute();
    }

    /**
     * Add any transformations to the output and return it.
     * 
     * @param string $stream
     * @return string
     */
    private function transform(string $stream): string
    {
        return sprintf("%s<!-- Hyde Realtime Compiler proxied, compiled, and served this request in %sms -->",
            $stream,
            HydeRC::getExecutionTime());
    }

    /**
     * Get the compiled output stream so it can be output to the client.
     *
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }
}