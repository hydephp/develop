<?php

namespace Hyde\Testing\Framework\Feature\Actions;

use Hyde\Framework\Actions\PublishesHomepageView;
use Hyde\Framework\Contracts\ActionContract;
use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\PublishesHomepageView
 */
class PublishesHomepageViewTest extends TestCase
{
    // Test that the class implements the ActionContract
    public function test_implements_action_contract()
    {
        $this->assertInstanceOf(ActionContract::class, new PublishesHomepageView('foo'));
    }

    // Test that the $homePages array contains all the available home pages
    public function test_home_pages_array_contains_all_available_home_pages()
    {
        $array = PublishesHomepageView::$homePages;

        $files = glob(Hyde::vendorPath('resources/views/homepages/*.blade.php'));

        $this->assertEquals(sizeof($files), sizeof($array));
    }

    // Text execute method returns 404 if the supplied home page doesn't exist
    public function test_execute_method_returns404_if_home_page_does_not_exist()
    {
        $action = new PublishesHomepageView('foo');

        $this->assertEquals(404, $action->execute());
    }

    // Test execute method can publish the selected home page
    public function test_execute_method_can_publish_home_page()
    {
        unlink(Hyde::path('_pages/index.blade.php'));
        (new PublishesHomepageView('welcome'))->execute();
        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
    }
}
