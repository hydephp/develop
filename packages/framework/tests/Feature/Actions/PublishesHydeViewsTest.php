<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\PublishesHydeViews;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\PublishesHydeViews
 */
class PublishesHydeViewsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->backupDirectory(Hyde::path('resources/views/vendor/hyde'));
        $this->deleteDirectory(Hyde::path('resources/views/vendor/hyde'));
    }

    protected function tearDown(): void
    {
        $this->restoreDirectory(Hyde::path('resources/views/vendor/hyde'));

        parent::tearDown();
    }

    public function test_execute_method_returns_404_for_invalid_option_key()
    {
        $action = new PublishesHydeViews('invalid');
        $this->assertEquals(404, $action->execute());
    }

    public function test_action_publishes_view_directories()
    {
        (new PublishesHydeViews('layouts'))->execute();
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php'));
    }

    public function test_action_publishes_view_files()
    {
        unlink(Hyde::path('_pages/404.blade.php'));

        (new PublishesHydeViews('404'))->execute();
        $this->assertFileExists(Hyde::path('_pages/404.blade.php'));
    }
}
