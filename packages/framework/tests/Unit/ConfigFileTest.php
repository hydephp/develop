<?php

declare(strict_types=1);

use Hyde\Foundation\HydeCoreExtension;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;

test('default source directories values are defined', function () {
    test()->assertArrayHasKey('source_directories', getConfig());
});

test('default source directories values match declared values', function () {
    test()->assertSame(getConfig()['source_directories'], [
        HtmlPage::class => '_pages',
        BladePage::class => '_pages',
        MarkdownPage::class => '_pages',
        MarkdownPost::class => '_posts',
        DocumentationPage::class => '_docs',
    ]);
});

test('default source directories values cover all core extension classes', function () {
    test()->assertSame(getConfig()['source_directories'], collect(HydeCoreExtension::getPageClasses())
        ->mapWithKeys(fn ($pageClass) => [$pageClass => $pageClass::$sourceDirectory])
        ->toArray()
    );
});

function getConfig(): array
{
    return require Hyde::vendorPath('config/hyde.php');
}
