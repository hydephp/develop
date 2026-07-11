<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Features;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\Redirect;
use Hyde\Testing\UnitTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\HydeCoreExtension::class)]
class HydeCoreExtensionTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public function testClassExtendsExtensionClass()
    {
        $this->assertInstanceOf(HydeCoreExtension::class, new HydeCoreExtension());
    }

    public function testClassIsRegistered()
    {
        $this->assertContains(HydeCoreExtension::class, HydeKernel::getInstance()->getRegisteredExtensions());
    }

    public function testConfiguredRedirectsAreDiscovered()
    {
        config(['hyde.redirects' => ['old-page' => 'new-page']]);

        $pages = PageCollection::init(HydeKernel::getInstance());
        (new HydeCoreExtension())->discoverPages($pages);

        $this->assertEquals(new Redirect('old-page', 'new-page'), $pages->get('old-page'));
    }

    public function testGetPageClasses()
    {
        $this->assertSame([
            HtmlPage::class,
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ], HydeCoreExtension::getPageClasses());
    }

    public function testGetPageClassesDoesNotIncludeClassesForDisabledFeatures()
    {
        Features::mock('documentation-pages', false);

        $this->assertSame([
            HtmlPage::class,
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
        ], HydeCoreExtension::getPageClasses());
    }
}
