<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Pages\InMemoryPage;
use Hyde\Testing\TestCase;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestsBladeViews;

/**
 * @see resources/views/components/docs/hyde-search.blade.php
 */
class HydeSearchViewTest extends TestCase
{
    use TestsBladeViews;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRoute();
    }

    public function testComponentUsesVersionedSearchIndexForRenderedDocumentationPage()
    {
        config(['docs.versions' => ['1.x']]);

        $this->mockPage(new DocumentationPage('1.x/installation'), 'docs/1.x/installation');

        $this->view('hyde::components.docs.hyde-search')
            ->assertSee("initHydeSearch('../../docs/1.x/search.json')", false)
            ->assertDontSee("initHydeSearch('../../docs/search.json')", false);
    }

    public function testComponentFallsBackToRootSearchIndexForNonDocumentationVersionPages()
    {
        config(['docs.versions' => ['1.x']]);

        $this->mockPage(new InMemoryPage('index'), 'index');

        $this->view('hyde::components.docs.hyde-search')
            ->assertSee("initHydeSearch('docs/search.json')", false);
    }
}
