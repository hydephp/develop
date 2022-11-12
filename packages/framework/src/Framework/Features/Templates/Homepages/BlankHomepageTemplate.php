<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates\Homepages;

use Hyde\Framework\Features\Templates\PublishableView;

class BlankHomepageTemplate extends PublishableView
{
    protected static string $title = 'Blank Starter';
    protected static string $desc =  'resources/views/homepages/blank.blade.php';
    protected static string $path =  'A blank Blade template with just the base layout.';
}
