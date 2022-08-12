<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\FrontMatter\Schemas\BlogPostSchema;
use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Markdown;
use Hyde\Framework\PageCollection;

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

    /** @return \Hyde\Framework\PageCollection<\Hyde\Framework\Models\Pages\MarkdownPost> */
    public static function getLatestPosts(): PageCollection
    {
        return static::all()->sortByDesc('matter.date');
    }
}
