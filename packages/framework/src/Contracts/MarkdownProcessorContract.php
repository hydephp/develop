<?php

namespace Hyde\Framework\Contracts;

/**
 * @deprecated Use either the MarkdownPreProcessorContract or the MarkdownPostProcessorContract
 */
interface MarkdownProcessorContract
{
    /**
     * @param  string  $input  Markdown to be processed
     * @return string $output Processed Markdown output
     */
    public static function process(string $input): string;
}
