<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Hyde;
use Illuminate\Support\Facades\Artisan;

/**
 * Publish one or more of the Hyde Blade views.
 *
 * @deprecated Command is being refactored to inline the action.
 * @see \Hyde\Framework\Testing\Feature\Actions\PublishesHomepageViewTest
 */
class PublishesHydeViews
{
    public static array $options = [
        'layouts' => [
            'name' => 'Blade Layouts',
            'description' => 'Shared layout views, such as the app layout, navigation menu, and Markdown page templates.',
            'group' => 'hyde-layouts',
        ],
        'components' => [
            'name' => 'Blade Components',
            'description' => 'More or less self contained components, extracted for customizability and DRY code.',
            'group' => 'hyde-components',
        ],
        '404' => [
            'name' => '404 Page',
            'description' => 'A beautiful 404 error page by the Laravel Collective.',
            'group' => 'hyde-page-404',
        ],
    ];

    protected string $selected;

    public function __construct(string $selected)
    {
        $this->selected = $selected;
    }

    public function execute(): int
    {
        return Artisan::call('vendor:publish', [
            '--tag' => self::$options[$this->selected]['group'],
            '--force' => true,
        ]);
    }
}
