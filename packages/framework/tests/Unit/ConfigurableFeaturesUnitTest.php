<?php

declare(strict_types=1);

use Hyde\Facades\Features;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    $this->mockConfig();
    $this->needsKernel();
});

test('expect has method returns false when feature is disabled', function (string $method) {
    Config::set('hyde.features', []);
    \Hyde\Foundation\HydeKernel::setInstance(new \Hyde\Foundation\HydeKernel());

    $this->assertFalse(Features::$method(), "Method '$method' should return false when feature is not enabled");
})->with([
    'hasHtmlPages',
    'hasBladePages',
    'hasMarkdownPages',
    'hasMarkdownPosts',
    'hasDocumentationPages',
])->covers(Hyde\Facades\Features::class);

test('expect has method returns true when feature is enabled', function (string $method) {
    Config::set('hyde.features', [str($method)->kebab()->replace('has-', '')->toString()]);
    \Hyde\Foundation\HydeKernel::setInstance(new \Hyde\Foundation\HydeKernel());

    $this->assertTrue(Features::$method(), "Method '$method' should return true when feature is enabled");
})->with([
    'hasHtmlPages',
    'hasBladePages',
    'hasMarkdownPages',
    'hasMarkdownPosts',
    'hasDocumentationPages',
])->covers(Hyde\Facades\Features::class);
