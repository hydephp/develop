<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas\Constructors;

use Hyde\Framework\Actions\Constructors\FindsNavigationDataForPage;
use Hyde\Framework\Actions\Constructors\FindsTitleForPage;
use Hyde\Framework\Concerns\FrontMatter\Schemas\BlogPostSchema;
use Hyde\Framework\Concerns\FrontMatter\Schemas\DocumentationPageSchema;
use Hyde\Framework\Concerns\FrontMatter\Schemas\PageSchema;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Author;
use Hyde\Framework\Models\DateString;
use Hyde\Framework\Models\Image;
use Illuminate\Support\Str;

trait ConstructsPageSchemas
{
    protected function constructPageSchemas(): void
    {
        if ($this->usesSchema(PageSchema::class)) {
            $this->constructPageSchema();
        }

        if ($this->usesSchema(BlogPostSchema::class)) {
            $this->constructBlogPostSchema();
        }

        if ($this->usesSchema(DocumentationPageSchema::class)) {
            $this->constructDocumentationPageSchema();
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

    protected function usesSchema(string $schema): bool
    {
        return in_array($schema, class_uses_recursive($this));
    }
}
