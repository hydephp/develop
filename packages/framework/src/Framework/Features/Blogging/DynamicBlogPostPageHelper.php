<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging;

use Hyde\Hyde;

/**
 * @internal Initial class to help with dynamic blogging related pages, like author pages, tag pages, etc.
 *
 * @experimental The code here will later be moved to a more appropriate place.
 */
class DynamicBlogPostPageHelper
{
    public static function canGenerateAuthorPages(): bool
    {
        return Hyde::authors()->isNotEmpty(); // Todo: && feature is enabled
    }

    /** @return array<\Hyde\Pages\InMemoryPage> */
    public static function generateAuthorPages(): array
    {
        return [];
    }
}
