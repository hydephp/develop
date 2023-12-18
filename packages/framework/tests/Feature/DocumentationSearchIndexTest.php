<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Pages\DocumentationPage;
use Hyde\Framework\Features\Documentation\DocumentationSearchIndex;

/**
 * @covers \Hyde\Framework\Features\Documentation\DocumentationSearchIndex
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\BuildSearchCommandTest
 */
class DocumentationSearchIndexTest extends TestCase
{
    public function testCanCreateDocumentationSearchIndexInstance()
    {
        $this->assertInstanceOf(DocumentationSearchIndex::class, new DocumentationSearchIndex());
    }

    public function testRouteKeyIsSetToDocumentationOutputDirectory()
    {
        $page = new DocumentationSearchIndex();
        $this->assertSame('docs/search.json', $page->routeKey);
    }

    public function testRouteKeyIsSetToConfiguredDocumentationOutputDirectory()
    {
        DocumentationPage::setOutputDirectory('foo');

        $page = new DocumentationSearchIndex();
        $this->assertSame('foo/search.json', $page->routeKey);
    }

    public function testStaticRouteKeyHelper()
    {
        $this->assertSame('docs/search.json', DocumentationSearchIndex::routeKey());
    }

    public function testStaticRouteKeyHelperWithCustomOutputDirectory()
    {
        DocumentationPage::setOutputDirectory('foo');
        $this->assertSame('foo/search.json', DocumentationSearchIndex::routeKey());
    }

    public function testStaticRouteKeyHelperWithRootOutputDirectory()
    {
        DocumentationPage::setOutputDirectory('');
        $this->assertSame('search.json', DocumentationSearchIndex::routeKey());
    }
}
