<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Features;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Foundation\HydeKernel;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;
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
