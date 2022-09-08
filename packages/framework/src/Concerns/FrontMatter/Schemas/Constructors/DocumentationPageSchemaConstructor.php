<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas\Constructors;

use Hyde\Framework\Hyde;
use Illuminate\Support\Str;

trait DocumentationPageSchemaConstructor
{
    protected function constructDocumentationPageSchema(): void
    {
        $this->category = $this->getDocumentationPageCategory();

        $this->label = $this->matter('label', Hyde::makeTitle(basename($this->identifier)));
        $this->hidden = $this->matter('hidden', $this->identifier === 'index');
        $this->priority = $this->matter('priority', $this->findPriorityInConfig());
    }

    protected function getDocumentationPageCategory(): ?string
    {
        // If the documentation page is in a subdirectory,
        // then we can use that as the category name.
        // Otherwise, we look in the front matter.

        return str_contains($this->identifier, '/')
            ? Str::before($this->identifier, '/')
            : $this->matter('category', 'other');
    }

    protected function findPriorityInConfig(): int
    {
        $orderIndexArray = config('docs.sidebar_order', []);

        if (! in_array($this->identifier, $orderIndexArray)) {
            return 500;
        }

        return array_search($this->identifier, $orderIndexArray) + 250;

        // Adding 250 makes so that pages with a front matter priority that is lower
        // can be shown first. It's lower than the fallback of 500 so that they
        // still come first. This is all to make it easier to mix priorities.
    }
}
