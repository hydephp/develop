<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging;

use Hyde\Hyde;
use Hyde\Enums\Feature;
use Hyde\Pages\MarkdownPost;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Framework\Features\Blogging\DynamicPages\PostAuthorPage;

/**
 * @internal Initial class to help with dynamic blogging related pages, like author pages, tag pages, etc.
 *
 * @experimental The code here will later be moved to a more appropriate place.
 */
class DynamicBlogPostPageHelper
{
    public static function canGenerateAuthorPages(): bool
    {
        // Todo: Also check that this feature is enabled

        return Hyde::hasFeature(Feature::MarkdownPosts) && Hyde::authors()->isNotEmpty() && MarkdownPost::all()->isNotEmpty();
    }

    /** @return array<\Hyde\Framework\Features\Blogging\DynamicPages\PostAuthorPage> */
    public static function generateAuthorPages(): array
    {
        // Todo: This does not find authors that have no author config, we should add those to the underlying collection!

        return Hyde::authors()
            ->map(fn (PostAuthor $author): PostAuthorPage => new PostAuthorPage("author/$author->username"))->all();
    }
}
