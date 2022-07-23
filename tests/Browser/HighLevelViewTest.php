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

    public function test_documentation_site_with_grouped_pages()
    {
        $this->makeDocumentationTestPage('Page1', ['category' => 'Group 1'], true);
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

    protected function makeDocumentationTestPage(string $name, ?array $matter = null, bool $withText = false)
    {
        $path = Hyde::path('_docs/'.Str::slug($name).'.md');

        $contents = '';

        if ($matter !== null) {
            $contents = (new ConvertsArrayToFrontMatter())->execute($matter)."\n";
        }

        $contents .= '# '.$name;

        if ($withText) {
            $contents .= "\n\n" . $this->getLoremMarkdownum();
        }

        file_put_contents($path, $contents);

        return $path;
    }

    protected function getLoremMarkdownum()
    {
        return <<<EOT
<p class="lead">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>

## Illam creavit crine

[Lorem markdownum](https://jaspervdj.be/lorem-markdownum) talia,
erit tempora **et rupit** proque. Ego **tamen** has Solis atque Laurenti ripas,
Riphea discrimine, verba. Sospes hora ab genis.

>info Lorem ipsum dolor sit amet, consectetur adipiscing elit.

- Proceres Boreas Hymenaeus ullas
- Faenilibus at alte quod

1. In formidatis filis tincto
2. Miseram Tenedos utque

## Hoc pro manibus saltem summum

> Longa inmanis donisque. Non falsi leaena adsumus admoto vultum, signorum

Tellusque vitare capiti quod ut urbe, **coniurataeque** spernite te turbasti.
Exspiravit commune leto iam siquis magos medere prolemque dentes fibras.

    architectureRuntime = 93;
    if (3 <= kdeIeeePassword) {
        rateShortcutTopology -= storage_firmware;
        memoryWysiwygTiger(key_ppga_southbridge(case_printer_checksum, wan, 5),
                firmware, 1);
        file = services(media, default) * 48 - vdslFriendly;
    }
    var scsiPixelIpv = dockingSkuZero;

*Tulit genitus* et ipsum simulacra natos quis sedes **curvo**; te te, utinam
premitur tradunt dextera illius ventris [in](https://www.foret.io/et.html)!
[Undis](https://veros.com/ora-heu.aspx) oculis; aliter iras; **est** siste
augusta: minis vanum captum prohibent ferrumque parsque ruunt usum.

Primo videtur Aethionque ignarus ceu adspicit, coma quam doleam guttura tectam.
Corpus quoque aeratis, at est indiciique regentis alterius deposuitque ademptas
membra residens gradus omnipotens illic caecae fortibus certa. Augerem diu et
geminae flerunt, est tecum tangeret silvas collo adspexit, *regna*?

EOT;
    }

}
