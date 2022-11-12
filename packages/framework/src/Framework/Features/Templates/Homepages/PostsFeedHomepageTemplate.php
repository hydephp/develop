<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates\Homepages;

use Hyde\Framework\Features\Templates\PublishableView;

class PostsFeedHomepageTemplate extends PublishableView
{
    protected static string $title = 'Posts Feed';
    protected static string $desc =  'resources/views/homepages/post-feed.blade.php';
    protected static string $path =  'A feed of your latest posts. Perfect for a blog site!';
}
