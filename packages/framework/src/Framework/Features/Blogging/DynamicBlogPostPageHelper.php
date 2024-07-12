<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging;

/**
 * @internal Initial class to help with dynamic blogging related pages, like author pages, tag pages, etc.
 * @experimental The code here will later be moved to a more appropriate place.
 *
 * @codeCoverageIgnore This feature is experimental and not yet tested.
 */
class DynamicBlogPostPageHelper
{
    public static function canGenerateAuthorPages(): bool
    {
        return true;
    }

    /**
     * @return array<\Hyde\Pages\InMemoryPage>
     */
    public static function generateAuthorPages(): array
    {
        //
    }
}
