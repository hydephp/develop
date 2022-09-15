<?php

namespace Hyde\Framework\Contracts;

/**
 * Process Markdown after it is converted to HTML.
 */
interface MarkdownPostProcessorContract
{
    /**
     * @param  string  $htmlInput  HTML to be processed
     * @return string $htmlOutput Processed HTML output
     */
    public static function postprocess(string $htmlInput): string;
}
