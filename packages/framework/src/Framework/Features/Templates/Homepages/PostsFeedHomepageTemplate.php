<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates\Homepages;

use Hyde\Framework\Features\Templates\PublishableView;

/** @internal */
class PostsFeedHomepageTemplate extends PublishableView
{
    protected static string $title = 'Posts Feed';
    protected static string $desc = 'A feed of your latest posts. Perfect for a blog site!';
    protected static string $path = 'resources/views/homepages/post-feed.blade.php';
    protected static ?string $outputPath = 'index.blade.php';
}
