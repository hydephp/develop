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
        $this->constructMetadata();
    }

    protected function constructPageSchemas(): void
    {
        parent::constructPageSchemas();
        $this->constructBlogPostSchema();
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
        if (! empty($this->description)) {
            $this->metadata['description'] = $this->description;
        }

        if ($this->author) {
            $this->metadata['author'] = $this->author->getName();
        }

        if ($this->category) {
            $this->metadata['keywords'] = $this->category;
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

        if ($this->title) {
            $this->properties['og:title'] = $this->title;
        }

        if ($this->matter('date') !== null) {
            $this->properties['og:article:published_time'] = $this->date->dateTimeObject->format('c');
        }

        if ($this->matter('image') !== null) {
            $this->setImageMetadata();
        }
    }

    protected function setImageMetadata(): void
    {
        if ($this->image) {
            if (str_starts_with($this->image->getLink(), 'http')) {
                $this->properties['og:image'] = $this->image->getLink();
            } else {
                $this->properties['og:image'] = '../media/'.basename($this->image->getLink());
            }
        }
    }
}
