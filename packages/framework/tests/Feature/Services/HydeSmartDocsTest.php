<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Features\Documentation\SemanticDocumentationArticle;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\HtmlString;

/**
 * @covers \Hyde\Framework\Features\Documentation\SemanticDocumentationArticle
 */
class HydeSmartDocsTest extends TestCase
{
    public function testClassTokenizesDocument()
    {
        $article = $this->makeArticle("# Header Content \n\n Body Content");

        $this->assertEquals('<h1>Header Content</h1>', $article->renderHeader());
        $this->assertEquals('<p>Body Content</p>', $article->renderBody());
    }

    public function testClassCanHandleDocumentWithNoHeader()
    {
        $article = $this->makeArticle('Body Content');

        $this->assertEquals('', $article->renderHeader());
        $this->assertEquals('<p>Body Content</p>', $article->renderBody());
    }

    public function testClassCanHandleDocumentWithOnlyHeader()
    {
        $article = $this->makeArticle('# Header Content');

        $this->assertEquals('<h1>Header Content</h1>', $article->renderHeader());
        $this->assertEquals('', $article->renderBody());
    }

    public function testClassCanHandleEmptyDocument()
    {
        $article = $this->makeArticle('');

        $this->assertEquals('', $article->renderHeader());
        $this->assertEquals('', $article->renderBody());
    }

    public function testCreateHelperCreatesNewInstanceAndProcessesIt()
    {
        $article = $this->makeArticle();

        $this->assertInstanceOf(SemanticDocumentationArticle::class, $article);

        $this->assertSame(
            '<p>Hello world.</p>',
            $article->renderBody()->toHtml()
        );
    }

    public function testRenderHeaderReturnsTheExtractedHeader()
    {
        $this->assertSame(
            '<h1>Foo</h1>',
            $this->makeArticle()->renderHeader()->toHtml()
        );
    }

    public function testRenderHeaderReturnsTheExtractedHeaderWithVaryingNewlines()
    {
        $tests = [
            "# Foo\n\nHello world.",
            "# Foo\r\n\r\nHello world.",
            "\n\n\n# Foo \r\n\r\n\n\n\n Hello world.",
        ];

        foreach ($tests as $test) {
            $this->assertSame(
                '<h1>Foo</h1>',
                $this->makeArticle($test)->renderHeader()->toHtml()
            );
        }
    }

    public function testRenderBodyReturnsTheExtractedBody()
    {
        $this->assertSame(
            '<p>Hello world.</p>',
            $this->makeArticle()->renderBody()->toHtml()
        );
    }

    public function testRenderBodyReturnsTheExtractedBodyWithVaryingNewlines()
    {
        $tests = [
            "# Foo\n\nHello world.",
            "# Foo\r\n\r\nHello world.",
            "\n\n\n# Foo \r\n\r\n\n\n\n Hello world.",
        ];

        foreach ($tests as $test) {
            $this->assertSame(
                '<p>Hello world.</p>',
                $this->makeArticle($test)->renderBody()->toHtml()
            );
        }
    }

    public function testRenderFooterIsEmptyByDefault()
    {
        $this->assertSame(
            '',
            $this->makeArticle()->renderFooter()->toHtml()
        );
    }

    public function testAddDynamicHeaderContentAddsSourceLinkWhenConditionsAreMet()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'header']);

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>
        HTML, $this->makeArticle()->renderHeader());
    }

    public function testEditSourceLinkIsAddedToFooterWhenConditionsAreMet()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'footer']);

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>
        HTML, $this->makeArticle()->renderFooter());
    }

    public function testEditSourceLinkCanBeAddedToBothHeaderAndFooter()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'both']);

        $article = $this->makeArticle();

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>
        HTML, $article->renderHeader());

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>
        HTML, $article->renderFooter());
    }

    public function testEditSourceLinkTextCanBeCustomizedInHeader()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'both']);
        config(['docs.edit_source_link_text' => 'Go to Source']);

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Go to Source</a></p>
        HTML, $this->makeArticle()->renderHeader());
    }

    public function testEditSourceLinkTextCanBeCustomizedInFooter()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'both']);
        config(['docs.edit_source_link_text' => 'Go to Source']);

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <p class="edit-page-link"><a href="https://example.com/foo.md">Go to Source</a></p>
        HTML, $this->makeArticle()->renderFooter());
    }

    public function testAddDynamicFooterContentAddsTorchlightAttributionWhenConditionsAreMet()
    {
        app()->bind('env', fn () => 'production');
        config(['torchlight.token' => '12345']);

        $this->assertStringContainsString('Syntax highlighting by <a href="https://torchlight.dev/"',
            $this->makeArticle('Syntax highlighted by torchlight.dev')->renderFooter()->toHtml()
        );
    }

    public function testTheDocumentationArticleView()
    {
        $rendered = view('hyde::components.docs.documentation-article', [
            'page' => $this->makePage(),
        ])->render();

        $this->assertStringContainsString('<h1>Foo</h1>', $rendered);
        $this->assertStringContainsString('<p>Hello world.</p>', $rendered);
    }

    public function testTheDocumentationArticleViewWithExistingVariable()
    {
        $rendered = view('hyde::components.docs.documentation-article', [
            'page' => $page = $this->makePage(),
            'article' => new class($page) extends SemanticDocumentationArticle
            {
                public function __construct(DocumentationPage $page)
                {
                    parent::__construct($page);
                }

                public function renderHeader(): HtmlString
                {
                    return new HtmlString('<h1>Custom Header</h1>');
                }
            },
        ])->render();

        $this->assertStringContainsString('<h1>Custom Header</h1>', $rendered);
        $this->assertStringContainsString('<p>Hello world.</p>', $rendered);
    }

    protected function makeArticle(string $sourceFileContents = "# Foo\n\nHello world."): SemanticDocumentationArticle
    {
        $this->file('_docs/foo.md', $sourceFileContents);

        return SemanticDocumentationArticle::make(DocumentationPage::parse('foo'));
    }

    protected function makePage(string $sourceFileContents = "# Foo\n\nHello world."): DocumentationPage
    {
        $this->file('_docs/foo.md', $sourceFileContents);

        return DocumentationPage::parse('foo');
    }

    protected function assertEqualsIgnoringNewlinesAndIndentation(string $expected, HtmlString $actual): void
    {
        $this->assertEquals(
            $this->stripNewlinesAndIndentation($expected),
            $this->stripNewlinesAndIndentation($actual->toHtml()),
        );
    }

    protected function stripNewlinesAndIndentation(string $string): string
    {
        return str_replace(["\r", "\n"], '', $this->stripIndentation($string));
    }

    protected function stripIndentation(string $string): string
    {
        return str_replace('    ', '', $string);
    }
}
