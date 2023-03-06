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

    public function testHydePageToArray()
    {
        $this->assertSame(
            ['identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title', 'canonicalUrl'],
            array_keys((new InstantiableHydePage())->toArray())
        );
    }

    public function testHtmlPageToArray()
    {
        $this->assertSame(
            ['identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title', 'canonicalUrl'],
            array_keys((new HtmlPage())->toArray())
        );
    }

    public function testBladePageToArray()
    {
        $this->assertSame(
            ['identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title', 'canonicalUrl'],
            array_keys((new BladePage())->toArray())
        );
    }

    public function testMarkdownPageToArray()
    {
        $this->assertSame(
            ['identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title', 'canonicalUrl'],
            array_keys((new MarkdownPage())->toArray())
        );
    }

    public function testMarkdownPostToArray()
    {
        $this->assertSame(
            ['identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title', 'canonicalUrl'],
            array_keys((new MarkdownPost())->toArray())
        );
    }

    public function testDocumentationPageToArray()
    {
        $this->assertSame(
            ['identifier', 'routeKey', 'matter', 'metadata', 'navigation', 'title', 'canonicalUrl'],
            array_keys((new DocumentationPage())->toArray())
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
