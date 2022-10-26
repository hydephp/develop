<?php
declare(strict_types=1);

namespace Hyde\Framework\Contracts;

/**
 * Process Markdown after it is converted to HTML.
 *
 * @see \Hyde\Framework\Contracts\MarkdownPreProcessorContract for pre-processing
 */
interface MarkdownPostProcessorContract
{
    /**
     * @param  string  $html  HTML to be processed
     * @return string $html Processed HTML output
     */
    public static function postprocess(string $html): string;
}
