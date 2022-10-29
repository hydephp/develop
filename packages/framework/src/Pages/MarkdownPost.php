<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Foundation\PageCollection;
use Hyde\Framework\Features\Blogging\Models\LegacyFeaturedImage;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Markdown\Contracts\FrontMatter\BlogPostSchema;
use Hyde\Pages\Concerns\BaseMarkdownPage;
use Hyde\Support\Models\DateString;

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

    public ?string $description;
    public ?string $category;
    public ?DateString $date;
    public ?PostAuthor $author;
    public ?LegacyFeaturedImage $image;

    /** @return \Hyde\Foundation\PageCollection<\Hyde\Pages\MarkdownPost> */
    public static function getLatestPosts(): PageCollection
    {
        return static::all()->sortByDesc('matter.date');
    }
}
