<?php

declare(strict_types=1);

use Hyde\Foundation\HydeCoreExtension;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;

beforeAll(function () {
    \Hyde\Foundation\HydeKernel::setInstance(new \Hyde\Foundation\HydeKernel());
});

test('test_default_source_directories_values_are_defined', function () {
    test()->assertArrayHasKey('source_directories', getConfig());
});

test('test_default_source_directories_values_match_declared_values', function () {
    test()->assertSame(getConfig()['source_directories'], [
        HtmlPage::class => '_pages',
        BladePage::class => '_pages',
        MarkdownPage::class => '_pages',
        MarkdownPost::class => '_posts',
        DocumentationPage::class => '_docs',
    ]);
});

test('test_default_source_directories_values_cover_all_core_extension_classes', function () {
    test()->assertSame(getConfig()['source_directories'], collect(HydeCoreExtension::getPageClasses())
        ->mapWithKeys(fn ($pageClass) => [$pageClass => $pageClass::$sourceDirectory])
        ->toArray()
    );
});

function getConfig(): array
{
    return require Hyde::vendorPath('config/hyde.php');
}
