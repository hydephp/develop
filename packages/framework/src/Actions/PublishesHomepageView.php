<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Hyde;

/**
 * Publish one of the Hyde homepages.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\PublishesHomepageViewTest
 */
class PublishesHomepageView
{
    public static array $homePages = [
        'welcome' => [
            'name' => 'Welcome',
            'path' => 'resources/views/homepages/welcome.blade.php',
            'description' => 'The default welcome page.',
        ],
        'posts' => [
            'name' => 'Posts Feed',
            'path' => 'resources/views/homepages/post-feed.blade.php',
            'description' => 'A feed of your latest posts. Perfect for a blog site!',
        ],
        'blank' => [
            'name' => 'Blank Starter',
            'path' => 'resources/views/homepages/blank.blade.php',
            'description' => 'A blank Blade template with just the base layout.',
        ],
    ];

    protected string $selected;

    public function __construct(string $selected)
    {
        $this->selected = $selected;
    }

    public function execute(): bool|int
    {
        if (! array_key_exists($this->selected, static::$homePages)) {
            return 404;
        }

        return copy(
            Hyde::vendorPath(static::$homePages[$this->selected]['path']),
            Hyde::getBladePagePath('index.blade.php')
        );
    }
}
