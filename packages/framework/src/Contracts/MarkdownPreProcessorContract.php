<?php

namespace Hyde\Framework\Contracts;

/**
 * Process Markdown before it is converted to HTML.
 */
interface MarkdownPreProcessorContract
{
    /**
     * @param  string  $markdown  Markdown to be processed
     * @return string $markdown Processed Markdown output
     */
    public static function preprocess(string $markdown): string;
}
