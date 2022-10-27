<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Actions\Constructors\FindsNavigationDataForPage;
use Hyde\Framework\Actions\Constructors\FindsTitleForPage;
use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Contracts\FrontMatter\BlogPostSchema;
use Hyde\Support\DateString;

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
        $this->navigation = FindsNavigationDataForPage::run($this);
        $this->canonicalUrl = $this->makeCanonicalUrl();
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
            $this->description = $this->matter('description', $this->makeDescription((string) $this->markdown));
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

    protected function getAuthor(): ?PostAuthor
    {
        if ($this->matter('author')) {
            return PostAuthor::make($this->matter('author'));
        }

        return null;
    }

    protected function getImage(): ?FeaturedImage
    {
        if ($this->matter('image')) {
            return FeaturedImage::make($this->matter('image'));
        }

        return null;
    }
}
