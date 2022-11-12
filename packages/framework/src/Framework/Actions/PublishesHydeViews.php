<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Hyde;
use Illuminate\Support\Facades\File;

/**
* Publish one or more of the Hyde Blade views.
*
* @see \Hyde\Framework\Testing\Feature\Actions\PublishesHomepageViewTest
*/
class PublishesHydeViews
{
    public static array $options = [
        'layouts' => [
            'name' => 'Blade Layouts',
            'path' => 'resources/views/layouts',
            'destination' => 'resources/views/vendor/hyde/layouts',
            'description' => 'Shared layout views, such as the app layout, navigation menu, and Markdown page templates.',
        ],
        'components' => [
            'name' => 'Blade Components',
            'path' => 'resources/views/components',
            'destination' => 'resources/views/vendor/hyde/components',
            'description' => 'More or less self contained components, extracted for customizability and DRY code.',
        ],
        '404' => [
            'name' => '404 Page',
            'path' => 'resources/views/pages/404.blade.php',
            'destination' => '_pages/404.blade.php',
            'description' => 'A beautiful 404 error page by the Laravel Collective.',
        ],
    ];

    protected string $selected;

    public function __construct(string $selected)
    {
        $this->selected = $selected;
    }

    public function execute(): bool|int
    {
        if (! array_key_exists($this->selected, static::$options)) {
            return 404;
        }

        if (is_dir(Hyde::vendorPath(static::$options[$this->selected]['path']))) {
            return File::copyDirectory(
                Hyde::vendorPath(static::$options[$this->selected]['path']),
                Hyde::path(static::$options[$this->selected]['destination'])
            );
        }

        return File::copy(
            Hyde::vendorPath(static::$options[$this->selected]['path']),
            Hyde::path(static::$options[$this->selected]['destination'])
        );
    }
}
