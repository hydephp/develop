<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates\Homepages;

use Hyde\Framework\Features\Templates\PublishableView;

/** @internal */
class WelcomeHomepageTemplate extends PublishableView
{
    protected static string $title = 'Welcome';
    protected static string $desc = 'The default welcome page.';
    protected static string $path = 'resources/views/homepages/welcome.blade.php';
    protected static ?string $outputPath = 'index.blade.php';
}
