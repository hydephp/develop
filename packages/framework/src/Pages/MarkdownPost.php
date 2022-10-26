<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Blogging\Models\Author;
use Hyde\Blogging\Models\FeaturedImage;
use Hyde\Foundation\PageCollection;
use Hyde\Framework\Concerns\BaseMarkdownPage;
use Hyde\Framework\Contracts\FrontMatter\BlogPostSchema;
use Hyde\Framework\Models\Support\DateString;

/**
 * Page class for Markdown posts.
 *
 * Markdown posts are stored in the _posts directory and using the .md extension.
 * The Markdown will be compiled to HTML using the blog post layout to the _site/posts/ directory.
 *
 * @see https://hydephp.com/docs/master/blog-posts
 */
class MarkdownPost extends BaseMarkdownPage implements BlogPostSchema
{
    public static string $sourceDirectory = '_posts';
    public static string $outputDirectory = 'posts';
    public static string $template = 'hyde::layouts/post';

    public string $title;
    public ?string $description = null;
    public ?string $category = null;
    public ?DateString $date = null;
    public ?Author $author = null;
    public ?FeaturedImage $image = null;

    /** @return \Hyde\Foundation\PageCollection<\Hyde\Framework\Models\Pages\MarkdownPost> */
    public static function getLatestPosts(): PageCollection
    {
        return static::all()->sortByDesc('matter.date');
    }
}
