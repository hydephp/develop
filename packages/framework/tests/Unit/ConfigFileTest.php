<?php

declare(strict_types=1);

use Hyde\Facades\Features;
use Hyde\Facades\Site;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;

test('default output directory value matches declared value', function () {
    expect(getConfig('output_directory'))->toBe(Site::getOutputDirectory());
});

test('default media directory value matches declared value', function () {
    expect(getConfig('media_directory'))->toBe(Hyde::getMediaDirectory());
});

test('default source root value matches declared value', function () {
    expect(getConfig('source_root'))->toBe(Hyde::getSourceRoot());
});

test('default source directories values match declared values', function () {
    expect(getConfig('source_directories'))->toBe([
        HtmlPage::class => '_pages',
        BladePage::class => '_pages',
        MarkdownPage::class => '_pages',
        MarkdownPost::class => '_posts',
        DocumentationPage::class => '_docs',
    ]);
});

test('default source directories values cover all core extension classes', function () {
    expect(getConfig('source_directories'))->toBe(collect(HydeCoreExtension::getPageClasses())
        ->mapWithKeys(fn ($pageClass) => [$pageClass => $pageClass::$sourceDirectory])
        ->toArray()
    );
});

test('default output directories values match declared values', function () {
    expect(getConfig('output_directories'))->toBe([
        HtmlPage::class => '',
        BladePage::class => '',
        MarkdownPage::class => '',
        MarkdownPost::class => 'posts',
        DocumentationPage::class => 'docs',
    ]);
});

test('default output directories values cover all core extension classes', function () {
    expect(getConfig('output_directories'))->toBe(collect(HydeCoreExtension::getPageClasses())
        ->mapWithKeys(fn ($pageClass) => [$pageClass => $pageClass::$outputDirectory])
        ->toArray()
    );
});

test('default features array matches default features', function () {
    expect(getConfig('features'))->toBe(
        (new ReflectionClass(Features::class))
            ->getMethod('getDefaultOptions')->invoke(null)
    );
});

function getConfig(string $option): mixed
{
    return (require Hyde::vendorPath('config/hyde.php'))[$option];
}
