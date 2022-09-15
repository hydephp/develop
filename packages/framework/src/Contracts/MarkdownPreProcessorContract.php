<?php

namespace Hyde\Framework\Contracts;

interface MarkdownPreProcessorContract
{
    /**
     * @param  string  $input  Markdown to be processed
     * @return string $output Processed Markdown output
     */
    public static function preprocess(string $input): string;
}
