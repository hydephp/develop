<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\AbstractMarkdownPage;
use Hyde\Framework\Concerns\FrontMatter\Schemas\BlogPostSchema;
use Hyde\Framework\Concerns\FrontMatter\Schemas\Constructors\BlogPostSchemaConstructor;
use Hyde\Framework\Foundation\PageCollection;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Markdown;

/**
 * @see \Hyde\Framework\Testing\Feature\MarkdownPostTest
 */
class MarkdownPost extends AbstractMarkdownPage
{
    use BlogPostSchema;
    use BlogPostSchemaConstructor;

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

    /** @return \Hyde\Framework\Foundation\PageCollection<\Hyde\Framework\Models\Pages\MarkdownPost> */
    public static function getLatestPosts(): PageCollection
    {
        return static::all()->sortByDesc('matter.date');
    }
}
