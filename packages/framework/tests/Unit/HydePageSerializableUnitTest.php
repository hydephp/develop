<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\Concerns\HydePage;
use Hyde\Testing\UnitTestCase;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;

/**
 * @covers \Hyde\Pages\Concerns\HydePage
 */
class HydePageSerializableUnitTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public function testHydePageToArrayKeys()
    {
        $this->assertSame(
            ['class', 'identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title', 'canonicalUrl'],
            array_keys((new InstantiableHydePage())->toArray())
        );
    }

    public function testHtmlPageToArrayKeys()
    {
        $this->assertSame(
            ['class', 'identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title', 'canonicalUrl'],
            array_keys((new HtmlPage())->toArray())
        );
    }

    public function testBladePageToArrayKeys()
    {
        $this->assertSame(
            ['class', 'identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title', 'canonicalUrl', 'view'],
            array_keys((new BladePage())->toArray())
        );
    }

    public function testMarkdownPageToArrayKeys()
    {
        $this->assertSame(
            ['class', 'identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title', 'canonicalUrl'],
            array_keys((new MarkdownPage())->toArray())
        );
    }

    public function testMarkdownPostToArrayKeys()
    {
        $this->assertSame(
            ['class', 'identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title', 'canonicalUrl', 'description', 'category', 'date', 'author', 'image'],
            array_keys((new MarkdownPost())->toArray())
        );
    }

    public function testDocumentationPageToArrayKeys()
    {
        $this->assertSame(
            ['class', 'identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title', 'canonicalUrl'],
            array_keys((new DocumentationPage())->toArray())
        );
    }

    public function testHydePageToArrayContents()
    {
        $page = new InstantiableHydePage();
        $this->assertSame([
            'class' => InstantiableHydePage::class,
            'identifier' => $page->identifier,
            'routeKey' => $page->routeKey,
            'matter' => $page->matter,
            'metadata' => $page->metadata,
            'navigation' => $page->navigation,
            'title' => $page->title,
            'canonicalUrl' => $page->canonicalUrl,
        ],
            $page->toArray()
        );
    }

    public function testHtmlPageToArrayContents()
    {
        $page = new HtmlPage();
        $this->assertSame([
            'class' => HtmlPage::class,
            'identifier' => $page->identifier,
            'routeKey' => $page->routeKey,
            'matter' => $page->matter,
            'metadata' => $page->metadata,
            'navigation' => $page->navigation,
            'title' => $page->title,
            'canonicalUrl' => $page->canonicalUrl,
        ],
            $page->toArray()
        );
    }

    public function testBladePageToArrayContents()
    {
        $page = new BladePage();
        $this->assertSame([
            'class' => BladePage::class,
            'identifier' => $page->identifier,
            'routeKey' => $page->routeKey,
            'matter' => $page->matter,
            'metadata' => $page->metadata,
            'navigation' => $page->navigation,
            'title' => $page->title,
            'canonicalUrl' => $page->canonicalUrl,
            'view' => $page->view,
        ],
            $page->toArray()
        );
    }

    public function testMarkdownPageToArrayContents()
    {
        $page = new MarkdownPage();
        $this->assertSame([
            'class' => MarkdownPage::class,
            'identifier' => $page->identifier,
            'routeKey' => $page->routeKey,
            'matter' => $page->matter,
            'metadata' => $page->metadata,
            'navigation' => $page->navigation,
            'title' => $page->title,
            'canonicalUrl' => $page->canonicalUrl,
        ],
            $page->toArray()
        );
    }

    public function testMarkdownPostToArrayContents()
    {
        $page = new MarkdownPost();
        $this->assertSame([
            'class' => MarkdownPost::class,
            'identifier' => $page->identifier,
            'routeKey' => $page->routeKey,
            'matter' => $page->matter,
            'metadata' => $page->metadata,
            'navigation' => $page->navigation,
            'title' => $page->title,
            'canonicalUrl' => $page->canonicalUrl,
            'description' => $page->description,
            'category' => $page->category,
            'date' => $page->date,
            'author' => $page->author,
            'image' => $page->image,
        ],
            $page->toArray()
        );
    }

    public function testDocumentationPageToArrayContents()
    {
        $page = new DocumentationPage();
        $this->assertSame([
            'class' => DocumentationPage::class,
            'identifier' => $page->identifier,
            'routeKey' => $page->routeKey,
            'matter' => $page->matter,
            'metadata' => $page->metadata,
            'navigation' => $page->navigation,
            'title' => $page->title,
            'canonicalUrl' => $page->canonicalUrl,
        ],
            $page->toArray()
        );
    }
}

class InstantiableHydePage extends HydePage
{
    public function compile(): string
    {
        return '';
    }
}
