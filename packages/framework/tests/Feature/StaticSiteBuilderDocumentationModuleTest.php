<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;

/**
 * Test the documentation page compiler module.
 */
class StaticSiteBuilderDocumentationModuleTest extends TestCase
{
    protected DocumentationPage $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->page = DocumentationPage::make('test-page', [
            'title' => 'Adventures in Wonderland',
            'description' => 'All in the golden afternoon, full leisurely we glide.',
        ], <<<'MARKDOWN'
            ## CHAPTER I. DOWN THE RABBIT-HOLE.

            So she was considering in her own mind, as well as she could, for the hot day made her feel very sleepy and stupid.
            MARKDOWN
        );
    }

    protected function inspectHtml(array $expectedStrings, string $path = null)
    {
        StaticPageBuilder::handle($this->page);
        $stream = file_get_contents(Hyde::path($path ?? '_site/docs/test-page.html'));
        $this->cleanUpWhenDone($path ?? '_site/docs/test-page.html');

        foreach ($expectedStrings as $expectedString) {
            $this->assertStringContainsString($expectedString, $stream);
        }
    }

    public function test_can_create_page()
    {
        StaticPageBuilder::handle($this->page);

        $this->assertFileExists(Hyde::path('_site/docs/test-page.html'));

        unlink(Hyde::path('_site/docs/test-page.html'));
    }

    public function test_page_contains_expected_content()
    {
        $this->inspectHtml([
            'Adventures in Wonderland',
            '<h2>CHAPTER I. DOWN THE RABBIT-HOLE.<a id="chapter-i-down-the-rabbit-hole" href="#chapter-i-down-the-rabbit-hole" class="heading-permalink" aria-hidden="true" title="Permalink">#</a></h2>',
            '<p>So she was considering in her own mind, as well as she could',
        ]);
    }

    public function test_can_compile_page_to_root_output_directory()
    {
        DocumentationPage::setOutputDirectory('');

        $this->inspectHtml([
            'Adventures in Wonderland',
            '<h2>CHAPTER I. DOWN THE RABBIT-HOLE.<a id="chapter-i-down-the-rabbit-hole" href="#chapter-i-down-the-rabbit-hole" class="heading-permalink" aria-hidden="true" title="Permalink">#</a></h2>',
            '<p>So she was considering in her own mind, as well as she could',
        ], '_site/test-page.html');
    }
}
