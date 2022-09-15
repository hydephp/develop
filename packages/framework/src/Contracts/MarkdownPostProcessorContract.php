<?php

namespace Hyde\Framework\Contracts;

/**
 * Process Markdown after it is converted to HTML.
 */
interface MarkdownPostProcessorContract
{
    /**
     * @param  string  $html  HTML to be processed
     * @return string $html Processed HTML output
     */
    public static function postprocess(string $html): string;
}
