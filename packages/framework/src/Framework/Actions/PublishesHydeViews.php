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
