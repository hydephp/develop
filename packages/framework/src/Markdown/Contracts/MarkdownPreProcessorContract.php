<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts;

/**
 * Process Markdown before it is converted to HTML.
 *
 * @see \Hyde\Markdown\Contracts\MarkdownPostProcessorContract for post-processing
 */
interface MarkdownPreProcessorContract
{
    /**
     * @param  string  $markdown  Markdown to be processed
     * @return string $markdown Processed Markdown output
     */
    public static function preprocess(string $markdown): string;
}
