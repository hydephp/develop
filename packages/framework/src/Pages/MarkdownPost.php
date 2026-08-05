<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Markdown\Contracts\FrontMatter\BlogPostSchema;
use Hyde\Pages\Concerns\BaseMarkdownPage;
use Hyde\Support\Models\DateString;

use function array_merge;
use function time;

/**
 * Page class for Markdown posts.
 *
 * Markdown posts are stored in the _posts directory and using the .md extension.
 * The Markdown will be compiled to HTML using the blog post layout to the _site/posts/ directory.
 *
 * @see https://hydephp.com/docs/2.x/blog-posts
 */
class MarkdownPost extends BaseMarkdownPage implements BlogPostSchema
{
    public static string $sourceDirectory = '_posts';
    public static string $outputDirectory = 'posts';
    public static string $template = 'hyde::layouts/post';

    public ?string $description;
    public ?string $category;
    public ?DateString $date;
    public bool $draft;
    public ?PostAuthor $author;
    public ?FeaturedImage $image;

    /** @return \Hyde\Foundation\Kernel\PageCollection<\Hyde\Pages\MarkdownPost> */
    public static function getLatestPosts(): PageCollection
    {
        return static::all()->sortByDesc(function (self $post): int {
            return $post->date?->getTimestamp() ?? 0;
        });
    }

    /**
     * Determine if the post is a draft, meaning it has `draft: true` in its front matter.
     *
     * Drafts are excluded from publication builds while the property is true, but remain available in realtime
     * compiler previews. Unlike a scheduled post, a draft never becomes publishable on its own, as it stays
     * excluded pending an explicit change to the draft property rather than a date.
     *
     * Posts are published by default, so `false` is equivalent to omitting the property.
     */
    public function isDraft(): bool
    {
        return $this->draft;
    }

    /**
     * Determine if the post is scheduled, meaning its date is set in the future.
     *
     * Scheduled posts are excluded from publication builds until their date has passed,
     * but remain available in realtime compiler previews.
     *
     * Since Hyde is a static site generator, a scheduled post does not publish itself when its date passes.
     * It is included in the first site build that is run after that point, so recurring builds are needed
     * for automatic publication, for example through a scheduled GitHub Actions workflow.
     */
    public function isScheduled(): bool
    {
        return $this->date !== null && $this->date->dateTimeObject->getTimestamp() > time();
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'description' => $this->description,
            'category' => $this->category,
            'date' => $this->date,
            'draft' => $this->draft,
            'author' => $this->author,
            'image' => $this->image,
        ]);
    }
}
