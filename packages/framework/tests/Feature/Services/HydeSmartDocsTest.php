<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use function app;
use function config;
use Hyde\Framework\Features\Documentation\SemanticDocumentationArticle;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\HtmlString;
use function str_replace;
use function view;

/**
 * @covers \Hyde\Framework\Features\Documentation\SemanticDocumentationArticle
 */
class HydeSmartDocsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        file_put_contents(Hyde::path('_docs/foo.md'), "# Foo\n\nHello world.");
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unlink(Hyde::path('_docs/foo.md'));
    }

    public function test_create_helper_creates_new_instance_and_processes_it()
    {
        $page = SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));

        $this->assertInstanceOf(SemanticDocumentationArticle::class, $page);

        $this->assertEqualsIgnoringNewlines('<p>Hello world.</p>', $page->renderBody());
    }

    public function test_instance_can_be_constructed_directly_with_same_result_as_facade()
    {
        $class = new SemanticDocumentationArticle(DocumentationPage::parse('foo'));
        $facade = SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));

        // Baseline since we manually need to call the process method
        $this->assertNotEquals($class, $facade);

        $class->process();

        // Now they should be the equal
        $this->assertEquals($class, $facade);
    }

    public function test_render_header_returns_the_extracted_header()
    {
        $page = SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));

        $this->assertEqualsIgnoringNewlines('<h1>Foo</h1>', $page->renderHeader());
    }

    public function test_render_header_returns_the_extracted_header_with_varying_newlines()
    {
        $tests = [
            "# Foo\n\nHello world.",
            "# Foo\r\n\r\nHello world.",
            "\n\n\n# Foo \r\n\r\n\n\n\n Hello world.",
        ];

        foreach ($tests as $test) {
            file_put_contents(Hyde::path('_docs/foo.md'), $test);
            $page = SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));
            $this->assertEqualsIgnoringNewlines('<h1>Foo</h1>', $page->renderHeader());
        }
    }

    public function test_render_body_returns_the_extracted_body()
    {
        $page = SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));

        $this->assertEqualsIgnoringNewlines('<p>Hello world.</p>', $page->renderBody());
    }

    public function test_render_body_returns_the_extracted_body_with_varying_newlines()
    {
        $tests = [
            "# Foo\n\nHello world.",
            "# Foo\r\n\r\nHello world.",
            "\n\n\n# Foo \r\n\r\n\n\n\n Hello world.",
        ];

        foreach ($tests as $test) {
            file_put_contents(Hyde::path('_docs/foo.md'), $test);
            $page = SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));
            $this->assertEqualsIgnoringNewlines('<p>Hello world.</p>', $page->renderBody());
        }
    }

    public function test_render_footer_is_empty_by_default()
    {
        $page = SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));

        $this->assertEqualsIgnoringNewlines('', $page->renderFooter());
    }

    public function test_add_dynamic_header_content_adds_source_link_when_conditions_are_met()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'header']);
        $page = SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>
        HTML, $page->renderHeader());
    }

    public function test_edit_source_link_is_added_to_footer_when_conditions_are_met()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'footer']);
        $page = SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>
        HTML, $page->renderFooter());
    }

    public function test_edit_source_link_can_be_added_to_both_header_and_footer()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'both']);

        $page = SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>
        HTML, $page->renderHeader());

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>
        HTML, $page->renderFooter());
    }

    public function test_edit_source_link_text_can_be_customized()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'both']);
        config(['docs.edit_source_link_text' => 'Go to Source']);

        $page = SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Go to Source</a></p>
        HTML, $page->renderHeader());

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <p class="edit-page-link"><a href="https://example.com/foo.md">Go to Source</a></p>
        HTML, $page->renderFooter());
    }

    public function test_add_dynamic_footer_content_adds_torchlight_attribution_when_conditions_are_met()
    {
        $this->mockTorchlight();
        file_put_contents(Hyde::path('_docs/foo.md'), 'Syntax highlighted by torchlight.dev');
        $page = SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));

        $this->assertStringContainsString('Syntax highlighting by <a href="https://torchlight.dev/"', $page->renderFooter()->toHtml());
    }

    public function test_the_documentation_article_view()
    {
        $rendered = view('hyde::components.docs.documentation-article', [
            'document' => SemanticDocumentationArticle::create(DocumentationPage::parse('foo')),
        ])->render();

        $this->assertStringContainsString('<h1>Foo</h1>', $rendered);
        $this->assertStringContainsString('<p>Hello world.</p>', $rendered);
    }

    protected function assertEqualsIgnoringNewlines(string $expected, HtmlString $actual): void
    {
        $this->assertEquals(
            $this->withoutNewLines($expected),
            $this->withoutNewLines($actual->toHtml())
        );
    }

    protected function assertEqualsIgnoringNewlinesAndIndentation(string $expected, HtmlString $actual): void
    {
        $this->assertEquals(
            $this->withoutNewLinesAndIndentation($expected),
            $this->withoutNewLinesAndIndentation($actual->toHtml())
        );
    }

    protected function withoutNewLines(string $expected): string
    {
        return str_replace(["\n", "\r"], '', $expected);
    }

    protected function withoutNewLinesAndIndentation(string $expected): string
    {
        return str_replace(["\n", "\r", '    '], '', $expected);
    }

    protected function mockTorchlight(): void
    {
        app()->bind('env', function () {
            return 'production';
        });

        config(['torchlight.token' => '12345']);
    }
}
