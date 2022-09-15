<?php

namespace Hyde\Framework\Contracts;

/**
 * Process Markdown after it is converted to HTML.
 */
interface MarkdownPostProcessorContract
{
    /**
     * @param  string  $input  HTML to be processed
     * @return string $output Processed HTML output
     */
    public static function postprocess(string $input): string;
}
