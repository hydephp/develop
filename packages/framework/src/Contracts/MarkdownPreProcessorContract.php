<?php

namespace Hyde\Framework\Contracts;

/**
 * Process Markdown before it is converted to HTML.
 */
interface MarkdownPreProcessorContract
{
    /**
     * @param  string  $input  Markdown to be processed
     * @return string $output Processed Markdown output
     */
    public static function preprocess(string $input): string;
}
