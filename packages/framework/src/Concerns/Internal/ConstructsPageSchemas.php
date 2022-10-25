<?php

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Actions\Constructors\FindsTitleForPage;
use Hyde\Framework\Contracts\FrontMatter\BlogPostSchema;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Support\Author;
use Hyde\Framework\Models\Support\DateString;
use Hyde\Framework\Models\Support\Image;

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

        $this->navigation = $this->constructNavigationData();
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
        if ($this instanceof MarkdownPost) {
            $this->category = $this->matter('category');
            $this->description = $this->matter('description', $this->makeDescription($this->markdown));
            $this->date = $this->matter('date') !== null ? new DateString($this->matter('date')) : null;
            $this->author = $this->getAuthor();
            $this->image = $this->getImage();
        }
    }

    protected function makeDescription(string $markdown): string
    {
        if (strlen($markdown) >= 128) {
            return substr($markdown, 0, 125).'...';
        }

        return $markdown;
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
