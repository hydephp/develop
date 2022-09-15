<?php

namespace Hyde\Framework\Contracts;

interface MarkdownPostProcessorContract
{
    /**
     * @param  string  $input  Html to be processed
     * @return string $output Processed Html output
     */
    public static function postprocess(string $input): string;
}
