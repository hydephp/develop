<?php

namespace Hyde\Testing\Browser;

use Hyde\Testing\DuskTestCase;
use Laravel\Dusk\Browser;

class DefaultHomepageTest extends DuskTestCase
{
    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Laravel');
        });
    }
}
