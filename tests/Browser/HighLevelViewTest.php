<?php

namespace Hyde\Testing\Browser;

use Hyde\Framework\Hyde;
use Hyde\Testing\DuskTestCase;
use Laravel\Dusk\Browser;

/**
 * Test the built in full-page views.
 * 
 * Each view generates a screenshot for visual analysis and regression testing.
 * Each view also saves the generated HTML for further testing, for example, Cypress.
 */
class HighLevelViewTest extends DuskTestCase
{
    public $mockConsoleOutput = false;

    public function test_welcome_homepage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('You\'re running on')
                    ->assertSee('HydePHP')
					->screenshot('welcome_homepage')
					->storeSource('welcome_homepage.html');
        });
    }

	public function test_404_page()
	{
		$this->browse(function (Browser $browser) {
			$browser->visit('/404')
					->assertSee('404')
					->assertSee('Sorry, the page you are looking for could not be found.')
					->screenshot('404_page')
					->storeSource('404_page.html');
		});
	}

	public function test_blank_homepage()
	{
		$this->artisan('publish:homepage blank -n');

		$this->browse(function (Browser $browser) {
			$browser->visit('/')
					->assertSee('HydePHP')
					->assertSee('Hello World!')
					->screenshot('blank_homepage')
					->storeSource('blank_homepage.html');
		});
	}

	public function test_posts_homepage()
	{
		$this->artisan('publish:homepage posts -n');
		
		$this->browse(function (Browser $browser) {
			$browser->visit('/')
					->assertSee('HydePHP')
					->assertSee('Latest Posts')
					->assertSeeNothingIn('#post-feed')
					->screenshot('posts_homepage')
					->storeSource('posts_homepage.html');
		});
	}

	public function test_posts_homepage_with_posts()
	{
		$this->artisan('publish:homepage posts -n');
		$this->artisan('make:post -n');

		$this->browse(function (Browser $browser) {
			$browser->visit('/')
					->assertSee('HydePHP')
					->assertSee('Latest Posts')
					->assertSee('My New Post')
					->screenshot('posts_homepage_with_posts')
					->storeSource('posts_homepage_with_posts.html');
		});

		unlink(Hyde::path('_posts/my-new-post.md'));
	}

	public function test_reset_state()
	{
		$this->artisan('publish:homepage welcome -n');
		unlink(Hyde::path('_site/index.html'));
		unlink(Hyde::path('_site/404.html'));
		$this->assertTrue(true);
	}
}
