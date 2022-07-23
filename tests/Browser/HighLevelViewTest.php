<?php

namespace Hyde\Testing\Browser;

use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use Hyde\Framework\Hyde;
use Hyde\Testing\DuskTestCase;
use Illuminate\Support\Str;
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
                    ->storeSourceAsHtml('welcome_homepage');
        });

        unlink(Hyde::path('_site/index.html'));
    }

    public function test_404_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/404')
                    ->assertSee('404')
                    ->assertSee('Sorry, the page you are looking for could not be found.')
                    ->screenshot('404_page')
                    ->storeSourceAsHtml('404_page');
        });

        unlink(Hyde::path('_site/404.html'));
    }

    public function test_blank_homepage()
    {
        $this->artisan('publish:homepage blank -n');

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('HydePHP')
                    ->assertSee('Hello World!')
                    ->screenshot('blank_homepage')
                    ->storeSourceAsHtml('blank_homepage');
        });

        $this->artisan('publish:homepage welcome -n');
        unlink(Hyde::path('_site/index.html'));
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
                    ->storeSourceAsHtml('posts_homepage');
        });

        $this->artisan('publish:homepage welcome -n');
        unlink(Hyde::path('_site/index.html'));
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
                    ->storeSourceAsHtml('posts_homepage_with_posts');
        });

        $this->artisan('publish:homepage welcome -n');
        unlink(Hyde::path('_posts/my-new-post.md'));
        unlink(Hyde::path('_site/index.html'));
    }

    public function test_documentation_index()
    {
        $this->artisan('make:page Index --type="documentation" -n');

        if (! is_dir(Browser::$storeSourceAt.'/docs')) {
            mkdir(Browser::$storeSourceAt.'/docs');
        }

        $this->browse(function (Browser $browser) {
            $browser->visit('/docs/index')
                ->assertSee('HydePHP Docs')
                ->assertNotPresent('#sidebar-navigation-menu > li')
                ->screenshot('docs/index')
                ->storeSourceAsHtml('docs/index');
        });

        unlink(Hyde::path('_docs/index.md'));
        unlink(Hyde::path('_site/docs/index.html'));
    }

    public function test_documentation_site_with_pages() {
        $this->artisan('make:page Page1 --type="documentation" -n');
        $this->artisan('make:page Page2 --type="documentation" -n');
        $this->artisan('make:page Page3 --type="documentation" -n');

        if (! is_dir(Browser::$storeSourceAt.'/docs')) {
            mkdir(Browser::$storeSourceAt.'/docs');
        }

        $this->browse(function (Browser $browser) {
            $browser->visit('/docs/page1')
                ->assertSee('HydePHP Docs')
                ->assertSee('Page1')
                ->assertSee('Page2')
                ->assertSee('Page3')
                ->assertPresent('#sidebar-navigation-menu > li.active')
                ->assertAriaAttribute('#sidebar-navigation-menu > li:nth-child(1) > a', 'current', 'true')
                ->screenshot('docs/with_sidebar_pages')
                ->storeSourceAsHtml('docs/with_sidebar_pages');
        });

        unlink(Hyde::path('_docs/page1.md'));
        unlink(Hyde::path('_docs/page2.md'));
        unlink(Hyde::path('_docs/page3.md'));
        unlink(Hyde::path('_site/docs/page1.html'));
    }

    public function test_documentation_site_with_grouped_pages() {
        $this->makeDocumentationTestPage('Page1', ['category' => 'Group 1']);
        $this->makeDocumentationTestPage('Page2', ['category' => 'Group 1']);
        $this->makeDocumentationTestPage('Page3');

        if (! is_dir(Browser::$storeSourceAt.'/docs')) {
            mkdir(Browser::$storeSourceAt.'/docs');
        }

        $this->browse(function (Browser $browser) {
            $browser->visit('/docs/page1')
                ->assertSee('HydePHP Docs')
                ->assertSee('Page1')
                ->assertSee('Page2')
                ->assertSee('Page3')
                ->assertAttribute('#sidebar-navigation-menu > li', 'class', 'sidebar-category')
                ->assertSeeIn('#sidebar-navigation-menu > li:nth-child(1) > h4.sidebar-category-heading', 'Group 1')
                ->assertAriaAttribute('#sidebar-navigation-menu > li:nth-child(1) > ul > li.sidebar-navigation-item.active > a', 'current', 'true')
                ->assertSeeIn('#sidebar-navigation-menu > li:nth-child(2) > h4.sidebar-category-heading', 'Other')
                ->screenshot('docs/with_grouped_sidebar_pages')
                ->storeSourceAsHtml('docs/with_grouped_sidebar_pages');
        });

        unlink(Hyde::path('_docs/page1.md'));
        unlink(Hyde::path('_docs/page2.md'));
        unlink(Hyde::path('_docs/page3.md'));
        unlink(Hyde::path('_site/docs/page1.html'));
    }

    protected function makeDocumentationTestPage(string $name, ?array $matter = null)
    {
        $path = Hyde::path('_docs/'.Str::slug($name).'.md');

        $contents = '';

        if ($matter !== null) {
            $contents = (new ConvertsArrayToFrontMatter())->execute($matter) . "\n";
        }

        $contents .= '# '.$name;

        file_put_contents($path, $contents);

        return $path;
    }
}
