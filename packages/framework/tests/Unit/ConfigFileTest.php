<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\HydeCoreExtension;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @see \Hyde\Framework\Testing\Unit\HydeConfigFilesAreMatchingTest
 */
class ConfigFileTest extends TestCase
{
    public function test_default_source_directories_values_are_defined()
    {
        $this->assertArrayHasKey('source_directories', $this->getConfig());
    }

    public function test_default_source_directories_values_match_declared_values()
    {
        $this->assertSame($this->getConfig()['source_directories'], [
            HtmlPage::class => '_pages',
            BladePage::class => '_pages',
            MarkdownPage::class => '_pages',
            MarkdownPost::class => '_posts',
            DocumentationPage::class => '_docs',
        ]);
    }

    public function test_default_source_directories_values_cover_all_core_extension_classes()
    {
        $this->assertSame($this->getConfig()['source_directories'], collect(HydeCoreExtension::getPageClasses())
            ->mapWithKeys(fn ($pageClass) => [$pageClass => $pageClass::$sourceDirectory])
            ->toArray()
        );
    }

    protected function getConfig(): array
    {
        return require Hyde::vendorPath('config/hyde.php');
    }
}
