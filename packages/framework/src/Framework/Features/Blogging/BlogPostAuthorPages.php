<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging;

use Hyde\Hyde;
use Hyde\Enums\Feature;
use Hyde\Pages\MarkdownPost;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Framework\Features\Blogging\DynamicPages\PostAuthorPage;
use Hyde\Framework\Features\Blogging\DynamicPages\PostAuthorsPage;

/**
 * General helper class for the dynamic blog post author pages feature.
 *
 * @internal Initial class to help with dynamic blogging related pages, like author pages, tag pages, etc.
 *
 * @experimental The code here will later be moved to a more appropriate place.
 */
class BlogPostAuthorPages
{
    public static function canGenerateAuthorPages(): bool
    {
        // Todo: Also check that this feature is enabled

        return Hyde::hasFeature(Feature::MarkdownPosts)
            && MarkdownPost::all()->isNotEmpty()
            && Hyde::authors()->filter(fn (PostAuthor $author): bool => $author->getPosts()->isNotEmpty())->isNotEmpty();
    }

    /** @return array<\Hyde\Framework\Features\Blogging\DynamicPages\PostAuthorPage> */
    public static function generateAuthorPages(): array
    {
        // Todo: This does not find authors that have no author config, we should add those to the underlying collection!

        $authors = Hyde::authors()
            // This filtering is opinionated, and we can configure it, but for now it only includes authors with posts
            // Todo: Unless the "guest" author has been modified, we should filter that too
            ->filter(fn (PostAuthor $author): bool => $author->getPosts()->isNotEmpty());

        return $authors
            ->map(fn (PostAuthor $author): PostAuthorPage => new PostAuthorPage($author))
            ->prepend(new PostAuthorsPage($authors))
            ->all();
    }

    public static function authorBaseRouteKey(): string
    {
        // Todo: Allow customizing this

        return 'authors';
    }
}
