<?php

namespace Hyde\Framework\Contracts;

interface MarkdownProcessorContract
{
    /**
     * @param  string  $input  Markdown to be processed
     * @return string $output Processed Markdown output
     */
    public static function process(string $input): string;
}
