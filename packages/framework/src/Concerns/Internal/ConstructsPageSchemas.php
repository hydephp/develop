<?php

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Actions\Constructors\FindsTitleForPage;
use Hyde\Framework\Contracts\FrontMatter\BlogPostSchema;
use Hyde\Framework\Contracts\FrontMatter\DocumentationPageSchema;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Image;
use Hyde\Framework\Models\Support\Author;
use Hyde\Framework\Models\Support\DateString;

trait ConstructsPageSchemas
{
    protected function constructPageSchemas(): void
    {
        $this->constructPageSchema();

        if ($this instanceof BlogPostSchema) {
            $this->constructBlogPostSchema();
        }
    }

    protected function constructPageSchema(): void
    {
        $this->title = FindsTitleForPage::run($this);
        $this->canonicalUrl = $this->makeCanonicalUrl();

        if ($this instanceof DocumentationPageSchema) {
            $this->constructSidebarNavigationData();
        } else {
            $this->constructNavigationData();
        }
    }

    protected function makeCanonicalUrl(): ?string
    {
        if (! empty($this->matter('canonicalUrl'))) {
            return $this->matter('canonicalUrl');
        }

        if (Hyde::hasSiteUrl() && ! empty($this->identifier)) {
            return $this->getRoute()->getQualifiedUrl();
        }

        return null;
    }

    protected function constructBlogPostSchema(): void
    {
        $this->category = $this->matter('category');
        $this->description = $this->matter('description', $this->makeDescription());
        $this->date = $this->matter('date') !== null ? new DateString($this->matter('date')) : null;
        $this->author = $this->getAuthor();
        $this->image = $this->getImage();
    }

    protected function makeDescription(): string
    {
        if (strlen($this->markdown) >= 128) {
            return substr($this->markdown, 0, 125).'...';
        }

        return (string) $this->markdown;
    }

    protected function getAuthor(): ?Author
    {
        if ($this->matter('author')) {
            return Author::make($this->matter('author'));
        }

        return null;
    }

    protected function getImage(): ?Image
    {
        if ($this->matter('image')) {
            return Image::make($this->matter('image'));
        }

        return null;
    }
}
