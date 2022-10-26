<?php
declare(strict_types=1);

namespace Hyde\Testing\Browser;

use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use Hyde\Framework\Hyde;
use Hyde\Testing\DuskTestCase;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;

/**
 * Test the built-in full-page views.
 *
 * Each view generates a screenshot for visual analysis and regression testing.
 * Each view also saves the generated HTML for further testing, for example, Cypress.
 */
class HighLevelViewTest extends DuskTestCase
{
    public $mockConsoleOutput = false;

    protected function tearDown(): void
    {
        parent::tearDown();
        sleep(1);
    }

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
        file_put_contents(Hyde::path('_posts/my-new-post.md'),
        '---
title: My New Post
description: A short description used in previews and SEO
category: blog
author: Mr. Hyde
date: 2022-01-01 12:00
---
## Write something awesome.

');

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
                ->assertNotPresent('#sidebar > li')
                ->screenshot('docs/index')
                ->storeSourceAsHtml('docs/index');
        });

        unlink(Hyde::path('_docs/index.md'));
        unlink(Hyde::path('_site/docs/index.html'));
    }

    public function test_documentation_site_with_pages()
    {
        $this->makeDocumentationTestPage('Page1', withText: true);
        $this->makeDocumentationTestPage('Page2');
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
                ->assertPresent('#sidebar-navigation > li.active')
                ->assertAriaAttribute('#sidebar-navigation > li:nth-child(1) > a', 'current', 'true')
                ->screenshot('docs/with_sidebar_pages')
                ->storeSourceAsHtml('docs/with_sidebar_pages');
        });

        unlink(Hyde::path('_docs/page1.md'));
        unlink(Hyde::path('_docs/page2.md'));
        unlink(Hyde::path('_docs/page3.md'));
        unlink(Hyde::path('_site/docs/page1.html'));
    }

    public function test_documentation_site_with_grouped_pages()
    {
        $this->makeDocumentationTestPage('Page1', ['navigation.group' => 'Group 1'], true);
        $this->makeDocumentationTestPage('Page2', ['navigation.group' => 'Group 1']);
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
                ->assertAttributeContains('#sidebar-navigation > li', 'class', 'sidebar-group')
                ->assertSeeIn('#sidebar-navigation > li:nth-child(1) > h4.sidebar-group-heading', 'Group 1')
                ->assertAriaAttribute('#sidebar-navigation > li:nth-child(1) > ul > li.sidebar-navigation-item.active > a', 'current', 'true')
                ->assertSeeIn('#sidebar-navigation > li:nth-child(2) > h4.sidebar-group-heading', 'Other')
                ->screenshot('docs/with_grouped_sidebar_pages')
                ->storeSourceAsHtml('docs/with_grouped_sidebar_pages');
        });

        unlink(Hyde::path('_docs/page1.md'));
        unlink(Hyde::path('_docs/page2.md'));
        unlink(Hyde::path('_docs/page3.md'));
        unlink(Hyde::path('_site/docs/page1.html'));
    }

    public function test_blog_post_pages()
    {
        copy(Hyde::path('tests/fixtures/_posts/typography-simple.md'), Hyde::path('_posts/typography-simple.md'));
        copy(Hyde::path('tests/fixtures/_posts/typography-front-matter.md'), Hyde::path('_posts/typography-front-matter.md'));

        $this->artisan('publish:homepage posts -n');

        if (! is_dir(Browser::$storeSourceAt.'/posts')) {
            mkdir(Browser::$storeSourceAt.'/posts');
        }

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('HydePHP')
                ->assertSee('Latest Posts')
                ->assertSee('Typography Should Be Easy.')
                ->assertSee('A TailwindCSS Typography demo with a bit more front matter, and a featured image.')
                ->assertSee('A TailwindCSS Typography demo with little front matter.')
                ->assertSee('Nov 7th, 2021, by TailwindLabs')
                ->screenshot('typography_blog_post_feed');

            $browser->assertSeeLink('Read post')
                ->clickLink('Typography Should Be Easy. Simple.')
                ->assertPathIs('/posts/typography-simple.html')
                ->assertSee('Typography Should Be Easy. Simple.')
                ->screenshot('typography_blog_post_page')
                ->storeSourceAsHtml('posts/typography-simple');

            $browser->back()
                ->assertPathIs('/')
                ->clickLink('Typography Should Be Easy. With Images.')
                ->assertPathIs('/posts/typography-front-matter.html')
                ->assertSee('Typography Should Be Easy. With Images.')
                ->assertSee('Posted Nov 7th, 2021 by author TailwindLabs in the category "testing"')
                ->assertPresent('article > figure > img')
                ->assertSee('Image by Blake. License the Unsplash License')
                ->assertSeeLink('TailwindLabs')
                ->assertSeeLink('Blake')
                ->assertSeeLink('the Unsplash License')
                ->screenshot('typography_blog_post_page_with_front_matter')
                ->storeSourceAsHtml('posts/typography-front-matter');
        });

        $this->artisan('publish:homepage welcome -n');

        unlink(Hyde::path('_posts/typography-simple.md'));
        unlink(Hyde::path('_posts/typography-front-matter.md'));
        unlink(Hyde::path('_site/posts/typography-simple.html'));
        unlink(Hyde::path('_site/posts/typography-front-matter.html'));
        unlink(Hyde::path('_site/index.html'));
    }

    protected function makeDocumentationTestPage(string $name, ?array $matter = null, bool $withText = false): string
    {
        $path = Hyde::path('_docs/'.Str::slug($name).'.md');

        $contents = '';

        if ($matter !== null) {
            $contents = (new ConvertsArrayToFrontMatter())->execute($matter)."\n";
        }

        $contents .= '# '.$name;

        if ($withText) {
            $contents .= "\n\n".file_get_contents(__DIR__.'/../fixtures/markdown-features.md');
        }

        file_put_contents($path, $contents);

        return $path;
    }
}
