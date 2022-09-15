<?php

namespace Hyde\Framework\Contracts;

/**
 * Process Markdown before it is converted to HTML.
 */
interface MarkdownPreProcessorContract
{
    /**
     * @param  string  $markdownInput  Markdown to be processed
     * @return string $markdownOutput Processed Markdown output
     */
    public static function preprocess(string $markdownInput): string;
}
