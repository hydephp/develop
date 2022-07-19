<?php

namespace Hyde\Testing\Browser;

use Laravel\Dusk\Browser;
use Hyde\Testing\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('You\'re running on')
                    ->assertSee('HydePHP');
        });
    }
}
