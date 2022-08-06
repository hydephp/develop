<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\FrontMatter\Schemas\BlogPostSchema;
use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Markdown;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Testing\Feature\MarkdownPostTest
 */
class MarkdownPost extends AbstractMarkdownPage
{
    use BlogPostSchema;

    public static string $sourceDirectory = '_posts';
    public static string $outputDirectory = 'posts';
    public static string $template = 'hyde::layouts/post';

    public function __construct(string $identifier = '', ?FrontMatter $matter = null, ?Markdown $markdown = null)
    {
        parent::__construct($identifier, $matter, $markdown);

        $this->constructBlogPostSchema();
        $this->constructMetadata();
    }

    /** @deprecated v0.58.x-beta (may be moved to BlogPostSchema) */
    public function getCanonicalLink(): string
    {
        return Hyde::url($this->getCurrentPagePath().'.html');
    }

    /** @deprecated v0.58.x-beta (pull description instead) */
    public function getPostDescription(): string
    {
        return $this->description;
    }

    public static function getLatestPosts(): Collection
    {
        return static::all()->sortByDesc('matter.date');
    }

    // HasArticleMetadata (Generates article metadata for a MarkdownPost)

    public array $metadata = [];
    public array $properties = [];

    protected function constructMetadata(): void
    {
        $this->parseFrontMatterMetadata();

        $this->makeOpenGraphPropertiesForArticle();
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
        if ($this->matter('description') !== null) {
            $this->metadata['description'] = $this->matter('description');
        }

        if ($this->matter('author') !== null) {
            $this->metadata['author'] = $this->getAuthorName($this->matter('author'));
        }

        if ($this->matter('category') !== null) {
            $this->metadata['keywords'] = $this->matter('category');
        }
    }

    /**
     * Generate opengraph metadata from front matter for an og:article such as a blog post.
     */
    protected function makeOpenGraphPropertiesForArticle(): void
    {
        $this->properties['og:type'] = 'article';
        if (Hyde::hasSiteUrl()) {
            $this->properties['og:url'] = $this->getRoute()->getQualifiedUrl();
        }

        if ($this->matter('title') !== null) {
            $this->properties['og:title'] = $this->matter('title');
        }

        if ($this->matter('date') !== null) {
            $this->properties['og:article:published_time'] = date('c', strtotime($this->matter('date')));
        }

        if ($this->matter('image') !== null) {
            $this->setImageMetadata();
        }
    }

    /**
     * Parse the author name string from front matter with support for both flat and array notation.
     *
     * @param  string|array  $author
     * @return string
     * @deprecated v0.58.x-beta (Use author model instead)
     */
    protected function getAuthorName(string|array $author): string
    {
        if (is_string($author)) {
            return $author;
        }

        return $author['name'] ?? $author['username'] ?? 'Guest';
    }

    protected function setImageMetadata(): void
    {
        if (is_string($this->matter('image'))) {
            $this->properties['og:image'] = $this->matter('image');
        } else {
            if (isset($this->matter('image')['path'])) {
                $this->properties['og:image'] = $this->matter('image')['path'];
            }
            if (isset($this->matter('image')['uri'])) {
                $this->properties['og:image'] = $this->matter('image')['uri'];
            }
        }
    }
}
