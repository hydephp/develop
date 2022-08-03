<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Hyde;

/**
 * Find and get the title to use for a Markdown Document.
 *
 * First check the front matter for a title. If one is not found,
 * it searches the Markdown for a level one heading. Falls back to
 * generating a title from the slug if no other title could be found.
 *
 * @deprecated Move to action to generate when constructing a page.
 */
trait HasDynamicTitle
{
    public function constructDynamicTitle(): void
    {
        if (! isset($this->title) || $this->title === '') {
            $this->title = $this->findTitleForDocument();
        }
    }

    public function findTitleForDocument(): string
    {
        if (isset($this->matter['title'])) {
            return $this->matter['title'];
        }

        return $this->findTitleTagInMarkdown($this->body)
            ?: Hyde::makeTitle($this->slug);
    }

    /**
     * Attempt to find the title based on the first H1 tag.
     */
    protected function findTitleTagInMarkdown(string $stream): string|false
    {
        $lines = explode("\n", $stream);

        foreach ($lines as $line) {
            if (str_starts_with($line, '# ')) {
                return trim(substr($line, 2), ' ');
            }
        }

        return false;
    }
}
