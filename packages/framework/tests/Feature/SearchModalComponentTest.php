<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Documentation\DocumentationSearchPage;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Documentation\DocumentationSearchPage
 * @covers \Hyde\Framework\Actions\GeneratesDocumentationSearchIndex
 */
class SearchModalComponentTest extends TestCase
{
  public function testSearchModalContainerHasXCloak()
  {
    $this->file('_docs/index.md');

    $page = DocumentationPage::make('index');
    Hyde::shareViewData($page);
    $html = $page->compile();

    $this->assertStringContainsString('x-cloak', $html);
    $this->assertStringContainsString('id="search-window-container"', $html);
    $this->assertStringContainsString('x-show="searchWindowOpen"', $html);
  }

  public function testDocumentationLayoutHasXDataWithSearchWindowOpen()
  {
    $this->file('_docs/foo.md');

    $page = DocumentationPage::make('foo');
    Hyde::shareViewData($page);
    $html = $page->compile();

    $this->assertStringContainsString('x-data="{ sidebarOpen: false, searchWindowOpen: false }"', $html);
    $this->assertStringContainsString('x-cloak', $html);
  }

  public function testDocumentationLayoutHasEscapeKeyHandler()
  {
    $this->file('_docs/foo.md');

    $page = DocumentationPage::make('foo');
    Hyde::shareViewData($page);
    $html = $page->compile();

    $this->assertStringContainsString('x-on:keydown.escape="searchWindowOpen = false; sidebarOpen = false"', $html);
  }

  public function testSearchModalComponentRendersInDocumentationPages()
  {
    $this->file('_docs/test.md');

    $page = DocumentationPage::make('test');
    Hyde::shareViewData($page);
    $html = $page->compile();

    // Test search window container structure
    $this->assertStringContainsString('<div id="search-window-container"', $html);
    $this->assertStringContainsString('x-show="searchWindowOpen"', $html);
    $this->assertStringContainsString('x-cloak', $html);
    $this->assertStringContainsString('role="dialog"', $html);

    // Test search window backdrop
    $this->assertStringContainsString('id="search-window-backdrop"', $html);

    // Test search buttons
    $this->assertStringContainsString('id="search-menu-button"', $html);
    $this->assertStringContainsString('id="search-menu-button-mobile"', $html);
  }

  public function testXCloakStylesAreIncluded()
  {
    $this->file('_docs/foo.md');

    $page = DocumentationPage::make('foo');
    Hyde::shareViewData($page);
    $html = $page->compile();

    $this->assertStringContainsString('[x-cloak] {display: none!important}', $html);
  }

  public function testSidebarBackdropHasXCloak()
  {
    $this->file('_docs/foo.md');

    $page = DocumentationPage::make('foo');
    Hyde::shareViewData($page);
    $html = $page->compile();

    $this->assertStringContainsString('id="sidebar-backdrop"', $html);
    $this->assertStringContainsString('x-cloak=""', $html);
    $this->assertStringContainsString('x-show="sidebarOpen"', $html);
  }

  public function testSidebarHasXCloak()
  {
    $this->file('_docs/foo.md');

    $page = DocumentationPage::make('foo');
    Hyde::shareViewData($page);
    $html = $page->compile();

    $this->assertStringContainsString('id="sidebar"', $html);
    $this->assertStringContainsString('x-cloak', $html);
  }

  public function testSearchModalButtonsHaveCorrectAttributes()
  {
    $this->file('_docs/test.md');

    $page = DocumentationPage::make('test');
    Hyde::shareViewData($page);
    $html = $page->compile();

    // Desktop search button
    $this->assertStringContainsString('x-on:click="searchWindowOpen = ! searchWindowOpen"', $html);
    $this->assertStringContainsString('aria-label="Toggle search window"', $html);

    // Mobile search button  
    $this->assertStringContainsString('aria-label="Toggle search menu"', $html);
  }

  public function testSearchPageHidesSearchButtonProperly()
  {
    $page = new DocumentationSearchPage();
    Hyde::shareViewData($page);
    $html = $page->compile();

    // The search page should hide the search button to prevent recursion
    $this->assertStringContainsString('#search-menu-button, .edit-page-link { display: none !important; }', $html);
  }

  public function testSearchWindowBackdropClickToClose()
  {
    $this->file('_docs/test.md');

    $page = DocumentationPage::make('test');
    Hyde::shareViewData($page);
    $html = $page->compile();

    $this->assertStringContainsString('title="Click to close search window"', $html);
    $this->assertStringContainsString('id="search-window-backdrop"', $html);
  }
}
