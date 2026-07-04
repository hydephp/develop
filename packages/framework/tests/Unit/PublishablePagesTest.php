<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Console\Helpers\PublishablePage;
use Hyde\Console\Helpers\PublishablePages;

use function array_values;
use function array_map;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Helpers\PublishablePages::class)]
class PublishablePagesTest extends UnitTestCase
{
    protected function setUp(): void
    {
        PublishablePages::clear();
    }

    protected function tearDown(): void
    {
        PublishablePages::clear();
    }

    public function testAllReturnsTheDefaultCatalog()
    {
        // Asserted via the ->key property since PHP coerces the numeric '404' array key to an integer.
        $this->assertSame(['welcome', 'posts', 'blank', '404'], $this->catalogKeys());
    }

    public function testAllReturnsPublishablePageInstancesRetrievableByTheirKey()
    {
        foreach (PublishablePages::all() as $page) {
            $this->assertInstanceOf(PublishablePage::class, $page);
            $this->assertSame($page, PublishablePages::get($page->key));
        }
    }

    public function testDefaultCatalogTargets()
    {
        $pages = PublishablePages::all();

        $this->assertSame('_pages/index.blade.php', $pages['welcome']->defaultTarget);
        $this->assertSame('_pages/posts.blade.php', $pages['posts']->defaultTarget);
        $this->assertSame('_pages/index.blade.php', $pages['blank']->defaultTarget);
        $this->assertSame('_pages/404.blade.php', $pages['404']->defaultTarget);
    }

    public function testDefaultCatalogSources()
    {
        $pages = PublishablePages::all();

        $this->assertSame('resources/views/homepages/welcome.blade.php', $pages['welcome']->source);
        $this->assertSame('resources/views/homepages/post-feed.blade.php', $pages['posts']->source);
        $this->assertSame('resources/views/homepages/blank.blade.php', $pages['blank']->source);
        $this->assertSame('resources/views/pages/404.blade.php', $pages['404']->source);
    }

    public function testOnlyPostsDeclaresAnAlternativeHomepageTarget()
    {
        $pages = PublishablePages::all();

        $this->assertSame(['_pages/index.blade.php' => 'Use as your site homepage'], $pages['posts']->alternativeTargets);
        $this->assertSame([], $pages['welcome']->alternativeTargets);
        $this->assertSame([], $pages['blank']->alternativeTargets);
        $this->assertSame([], $pages['404']->alternativeTargets);
    }

    public function testOnlyThe404PageForbidsCustomTargets()
    {
        $pages = PublishablePages::all();

        $this->assertTrue($pages['welcome']->allowCustomTarget);
        $this->assertTrue($pages['posts']->allowCustomTarget);
        $this->assertTrue($pages['blank']->allowCustomTarget);
        $this->assertFalse($pages['404']->allowCustomTarget);
    }

    public function testGetReturnsPageByKey()
    {
        $this->assertSame('welcome', PublishablePages::get('welcome')->key);
    }

    public function testGetReturnsNullForUnknownKey()
    {
        $this->assertNull(PublishablePages::get('does-not-exist'));
    }

    public function testRegisterAddsANewPage()
    {
        PublishablePages::register(new PublishablePage(
            key: 'changelog',
            label: 'Changelog page',
            description: 'A changelog for your site.',
            source: 'resources/views/homepages/blank.blade.php',
            defaultTarget: '_pages/changelog.blade.php',
        ));

        $this->assertArrayHasKey('changelog', PublishablePages::all());
        $this->assertSame('changelog', PublishablePages::get('changelog')->key);
        $this->assertSame(['welcome', 'posts', 'blank', '404', 'changelog'], $this->catalogKeys());
    }

    public function testRegisterOverridesAPageSharingItsKey()
    {
        PublishablePages::register(new PublishablePage(
            key: 'welcome',
            label: 'Custom welcome page',
            description: 'An overridden welcome page.',
            source: 'resources/views/homepages/blank.blade.php',
            defaultTarget: '_pages/index.blade.php',
        ));

        $this->assertSame('Custom welcome page', PublishablePages::get('welcome')->label);
        $this->assertCount(4, PublishablePages::all());
    }

    /** @return array<int, string> The page keys read from the ->key property (immune to PHP numeric-key coercion). */
    protected function catalogKeys(): array
    {
        return array_values(array_map(fn (PublishablePage $page): string => $page->key, PublishablePages::all()));
    }
}
