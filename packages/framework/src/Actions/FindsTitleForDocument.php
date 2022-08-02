<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Hyde;

/**
 * Replaces @see \Hyde\Framework\Concerns\HasDynamicTitle.
 */
class FindsTitleForDocument
{
    public static function get(string $slug = '', array $matter = [], string $markdown = ''): string
    {
        if (isset($matter['title'])) {
            return $matter['title'];
        }

        return static::findTitleTagInMarkdown($markdown)
            ?: Hyde::makeTitle($slug);
    }

    /** Attempt to find the title based on the first H1 tag. */
    protected function findTitleTagInMarkdown(string $markdown): string|false
    {
        $lines = explode("\n", $markdown);

        foreach ($lines as $line) {
            if (str_starts_with($line, '# ')) {
                return trim(substr($line, 2), ' ');
            }
        }

        return false;
    }
}
