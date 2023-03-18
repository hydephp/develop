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
 * @covers \Hyde\Pages\HtmlPage
 * @covers \Hyde\Pages\BladePage
 * @covers \Hyde\Pages\MarkdownPage
 * @covers \Hyde\Pages\MarkdownPost
 * @covers \Hyde\Pages\DocumentationPage
 */
class HydePageSerializableUnitTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public function testToArray()
    {
        $this->assertIsArray((new InstantiableHydePage())->toArray());
    }

    public function testJsonSerializeUsesArraySerialize()
    {
        $page = new InstantiableHydePage();
        $this->assertSame($page->arraySerialize(), $page->jsonSerialize());
    }

    public function testToJsonUsesJsonEncodedObject()
    {
        $page = new InstantiableHydePage();
        $this->assertSame(json_encode($page), $page->toJson());
    }

    public function testToJsonUsesJsonEncodedArray()
    {
        $page = new InstantiableHydePage();
        $this->assertSame(json_encode($page->toArray()), $page->toJson());
    }

    public function testHydePageToArrayKeys()
    {
        $this->assertSame(
            ['class', 'identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title'],
            array_keys((new InstantiableHydePage())->toArray())
        );
    }

    public function testHtmlPageToArrayKeys()
    {
        $this->assertSame(
            ['class', 'identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title'],
            array_keys((new HtmlPage())->toArray())
        );
    }

    public function testBladePageToArrayKeys()
    {
        $this->assertSame(
            ['class', 'identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title'],
            array_keys((new BladePage())->toArray())
        );
    }

    public function testMarkdownPageToArrayKeys()
    {
        $this->assertSame(
            ['class', 'identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title'],
            array_keys((new MarkdownPage())->toArray())
        );
    }

    public function testMarkdownPostToArrayKeys()
    {
        $this->assertSame(
            ['class', 'identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title', 'description', 'category', 'date', 'author', 'image'],
            array_keys((new MarkdownPost())->toArray())
        );
    }

    public function testDocumentationPageToArrayKeys()
    {
        $this->assertSame(
            ['class', 'identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title'],
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
        ],
            $page->toArray()
        );
    }

    public function testJsonEncodedOutput()
    {
        $this->assertSame(<<<'JSON'
            {
                "class": "Hyde\\Framework\\Testing\\Unit\\InstantiableHydePage",
                "identifier": "",
                "routeKey": "",
                "matter": [],
                "metadata": {},
                "navigation": {
                    "label": "",
                    "priority": 999,
                    "hidden": false,
                    "group": null
                },
                "title": ""
            }
            JSON, (new InstantiableHydePage())->toJson(128)
        );
    }

    public function testJsonSerializedHydePageContents()
    {
        $this->assertSame(<<<'JSON'
            {
                "class": "Hyde\\Framework\\Testing\\Unit\\InstantiableHydePage",
                "identifier": "",
                "routeKey": "",
                "matter": [],
                "metadata": {},
                "navigation": {
                    "label": "",
                    "priority": 999,
                    "hidden": false,
                    "group": null
                },
                "title": ""
            }
            JSON, (new InstantiableHydePage())->toJson(128)
        );
    }

    public function testJsonSerializedHtmlPageContents()
    {
        $this->assertSame(<<<'JSON'
            {
                "class": "Hyde\\Pages\\HtmlPage",
                "identifier": "",
                "routeKey": "",
                "matter": [],
                "metadata": {},
                "navigation": {
                    "label": "",
                    "priority": 999,
                    "hidden": false,
                    "group": null
                },
                "title": ""
            }
            JSON, (new HtmlPage())->toJson(128)
        );
    }

    public function testJsonSerializedBladePageContents()
    {
        $this->assertSame(<<<'JSON'
            {
                "class": "Hyde\\Pages\\BladePage",
                "identifier": "",
                "routeKey": "",
                "matter": [],
                "metadata": {},
                "navigation": {
                    "label": "",
                    "priority": 999,
                    "hidden": false,
                    "group": null
                },
                "title": ""
            }
            JSON, (new BladePage())->toJson(128)
        );
    }

    public function testJsonSerializedMarkdownPageContents()
    {
        $this->assertSame(<<<'JSON'
            {
                "class": "Hyde\\Pages\\MarkdownPage",
                "identifier": "",
                "routeKey": "",
                "matter": [],
                "metadata": {},
                "navigation": {
                    "label": "",
                    "priority": 999,
                    "hidden": false,
                    "group": null
                },
                "title": ""
            }
            JSON, (new MarkdownPage())->toJson(128)
        );
    }

    public function testJsonSerializedMarkdownPostContents()
    {
        $this->assertSame(<<<'JSON'
            {
                "class": "Hyde\\Pages\\MarkdownPost",
                "identifier": "",
                "routeKey": "posts",
                "matter": [],
                "metadata": {},
                "navigation": {
                    "label": "",
                    "priority": 10,
                    "hidden": true,
                    "group": null
                },
                "title": "",
                "description": "",
                "category": null,
                "date": null,
                "author": null,
                "image": null
            }
            JSON, (new MarkdownPost())->toJson(128)
        );
    }

    public function testJsonSerializedDocumentationPageContents()
    {
        $this->assertSame(<<<'JSON'
            {
                "class": "Hyde\\Pages\\DocumentationPage",
                "identifier": "",
                "routeKey": "docs",
                "matter": [],
                "metadata": {},
                "navigation": {
                    "label": "",
                    "priority": 999,
                    "hidden": false,
                    "group": "other"
                },
                "title": ""
            }
            JSON, (new DocumentationPage())->toJson(128)
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
