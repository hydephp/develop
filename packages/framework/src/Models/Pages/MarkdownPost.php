<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Contracts\FrontMatter\BlogPostSchema;
use Hyde\Framework\Foundation\PageCollection;
use Hyde\Framework\Models\Author;
use Hyde\Framework\Models\DateString;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Image;
use Hyde\Framework\Models\Markdown;

/**
 * @see \Hyde\Framework\Testing\Feature\MarkdownPostTest
 */
class MarkdownPost extends MarkdownPage implements BlogPostSchema
{
    public static string $sourceDirectory = '_posts';
    public static string $outputDirectory = 'posts';
    public static string $template = 'hyde::layouts/post';

    public string $title;
    public ?string $description = null;
    public ?string $category = null;
    public ?DateString $date = null;
    public ?Author $author = null;
    public ?Image $image = null;

    public function __construct(string $identifier = '', ?FrontMatter $matter = null, ?Markdown $markdown = null)
    {
        parent::__construct($identifier, $matter, $markdown);
    }

    /** @return \Hyde\Framework\Foundation\PageCollection<\Hyde\Framework\Models\Pages\MarkdownPost> */
    public static function getLatestPosts(): PageCollection
    {
        return static::all()->sortByDesc('matter.date');
    }
}
