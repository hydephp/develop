<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Documentation\DocumentationSearchPage;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Documentation\DocumentationSearchPage
 */
class DocumentationSearchPageTest extends TestCase
{
    public function testCanCreateDocumentationSearchPageInstance()
    {
        $this->assertInstanceOf(DocumentationSearchPage::class, new DocumentationSearchPage());
    }

    public function testIdentifierIsSetToDocumentationOutputDirectory()
    {
        $page = new DocumentationSearchPage();
        $this->assertSame('docs/search', $page->identifier);
    }

    public function testIdentifierIsSetToConfiguredDocumentationOutputDirectory()
    {
        DocumentationPage::$outputDirectory = 'foo';

        $page = new DocumentationSearchPage();
        $this->assertSame('foo/search', $page->identifier);

        DocumentationPage::$outputDirectory = 'docs';
    }
}
