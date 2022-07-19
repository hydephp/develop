<?php

namespace Hyde\Testing\Browser;

use Laravel\Dusk\Browser;
use Hyde\Testing\DuskTestCase;

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
