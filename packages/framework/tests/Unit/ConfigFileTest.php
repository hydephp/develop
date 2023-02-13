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
    expect(getConfig())->toHaveKey('source_directories');
});

test('default source directories values match declared values', function () {
    expect(getConfig()['source_directories'])->toBe([
        HtmlPage::class => '_pages',
        BladePage::class => '_pages',
        MarkdownPage::class => '_pages',
        MarkdownPost::class => '_posts',
        DocumentationPage::class => '_docs',
    ]);
});

test('default source directories values cover all core extension classes', function () {
    expect(getConfig()['source_directories'])->toBe(collect(HydeCoreExtension::getPageClasses())
        ->mapWithKeys(fn ($pageClass) => [$pageClass => $pageClass::$sourceDirectory])
        ->toArray()
    );
});

function getConfig(): array
{
    return require Hyde::vendorPath('config/hyde.php');
}
