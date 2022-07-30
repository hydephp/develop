<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\MarkdownPost;

/**
 * Generates metadata for page models that have front matter.
 *
 * @see \Hyde\Framework\Models\Metadata
 * @see \Hyde\Framework\Testing\Feature\Concerns\GeneratesPageMetadataTest
 */
trait GeneratesPageMetadata
{
    public array $metadata = [];
    public array $properties = [];

    public function constructMetadata(): void
    {
        $this->parseFrontMatterMetadata();

        if ($this instanceof MarkdownPost || isset($this->forceOpenGraph) && $this->forceOpenGraph) {
            $this->makeOpenGraphPropertiesForArticle();
        }
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getMetaProperties(): array
    {
        return $this->properties;
    }

    /**
     * Generate metadata from the front matter that can be used in standard <meta> tags.
     * This helper is page type agnostic and works with any kind of model having front matter.
     */
    protected function parseFrontMatterMetadata(): void
    {
        if (isset($this->matter['description'])) {
            $this->metadata['description'] = $this->matter['description'];
        }

        if (isset($this->matter['author'])) {
            $this->metadata['author'] = $this->getAuthorName($this->matter['author']);
        }

        if (isset($this->matter['category'])) {
            $this->metadata['keywords'] = $this->matter['category'];
        }
    }

    /**
     * Generate opengraph metadata from front matter for an og:article such as a blog post.
     */
    protected function makeOpenGraphPropertiesForArticle(): void
    {
        $this->properties['og:type'] = 'article';
        if (Hyde::hasSiteUrl()) {
            $this->properties['og:url'] = Hyde::url(Hyde::pageLink('posts/'.$this->slug.'.html'));
        }

        if (isset($this->matter['title'])) {
            $this->properties['og:title'] = $this->matter['title'];
        }

        if (isset($this->matter['date'])) {
            $this->properties['og:article:published_time'] = date('c', strtotime($this->matter['date']));
        }

        if (isset($this->matter['image'])) {
            if (is_string($this->matter['image'])) {
                $this->properties['og:image'] = $this->matter['image'];
            } else {
                if (isset($this->matter['image']['path'])) {
                    $this->properties['og:image'] = $this->matter['image']['path'];
                }
                if (isset($this->matter['image']['uri'])) {
                    $this->properties['og:image'] = $this->matter['image']['uri'];
                }
            }
        }
    }

    /**
     * Parse the author name string from front matter with support for both flat and array notation.
     *
     * @param  string|array  $author
     * @return string
     */
    protected function getAuthorName(string|array $author): string
    {
        if (is_string($author)) {
            return $author;
        }

        return $author['name'] ?? $author['username'] ?? 'Guest';
    }
}
