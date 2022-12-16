<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use function app;
use function config;
use Hyde\Framework\Features\Documentation\SemanticDocumentationArticle;
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
    public function test_class_tokenizes_document()
    {
        $article = $this->makeArticle("# Header Content \n\n Body Content");

        $this->assertEquals('<h1>Header Content</h1>', $article->renderHeader());
        $this->assertEquals('<p>Body Content</p>', $article->renderBody());
    }

    public function test_class_can_handle_document_with_no_header()
    {
        $article = $this->makeArticle('Body Content');

        $this->assertEquals('', $article->renderHeader());
        $this->assertEquals('<p>Body Content</p>', $article->renderBody());
    }

    public function test_class_can_handle_document_with_only_header()
    {
        $article = $this->makeArticle('# Header Content');

        $this->assertEquals('<h1>Header Content</h1>', $article->renderHeader());
        $this->assertEquals('', $article->renderBody());
    }

    public function test_class_can_handle_empty_document()
    {
        $article = $this->makeArticle('');

        $this->assertEquals('', $article->renderHeader());
        $this->assertEquals('', $article->renderBody());
    }

    public function test_create_helper_creates_new_instance_and_processes_it()
    {
        $article = $this->makeArticle();

        $this->assertInstanceOf(SemanticDocumentationArticle::class, $article);

        $this->assertSame(
            '<p>Hello world.</p>',
            $article->renderBody()->toHtml()
        );
    }

    public function test_instance_can_be_constructed_directly_with_same_result_as_facade()
    {
        $this->file('_docs/foo.md', "# Foo\n\nHello world.");

        $page = DocumentationPage::parse('foo');

        $this->assertEquals(
            new SemanticDocumentationArticle($page),
            SemanticDocumentationArticle::create($page)
        );
    }

    public function test_render_header_returns_the_extracted_header()
    {
        $this->assertSame(
            '<h1>Foo</h1>',
            $this->makeArticle()->renderHeader()->toHtml()
        );
    }

    public function test_render_header_returns_the_extracted_header_with_varying_newlines()
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

    public function test_render_body_returns_the_extracted_body()
    {
        $this->assertSame(
            '<p>Hello world.</p>',
            $this->makeArticle()->renderBody()->toHtml()
        );
    }

    public function test_render_body_returns_the_extracted_body_with_varying_newlines()
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

    public function test_render_footer_is_empty_by_default()
    {
        $this->assertSame(
            '',
            $this->makeArticle()->renderFooter()->toHtml()
        );
    }

    public function test_add_dynamic_header_content_adds_source_link_when_conditions_are_met()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'header']);

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>
        HTML, $this->makeArticle()->renderHeader());
    }

    public function test_edit_source_link_is_added_to_footer_when_conditions_are_met()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'footer']);

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>
        HTML, $this->makeArticle()->renderFooter());
    }

    public function test_edit_source_link_can_be_added_to_both_header_and_footer()
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

    public function test_edit_source_link_text_can_be_customized_in_header()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'both']);
        config(['docs.edit_source_link_text' => 'Go to Source']);

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Go to Source</a></p>
        HTML, $this->makeArticle()->renderHeader());
    }

    public function test_edit_source_link_text_can_be_customized_in_footer()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'both']);
        config(['docs.edit_source_link_text' => 'Go to Source']);

        $this->assertEqualsIgnoringNewlinesAndIndentation(<<<'HTML'
            <p class="edit-page-link"><a href="https://example.com/foo.md">Go to Source</a></p>
        HTML, $this->makeArticle()->renderFooter());
    }

    public function test_add_dynamic_footer_content_adds_torchlight_attribution_when_conditions_are_met()
    {
        app()->bind('env', fn () => 'production');
        config(['torchlight.token' => '12345']);

        $this->assertStringContainsString('Syntax highlighting by <a href="https://torchlight.dev/"',
            $this->makeArticle('Syntax highlighted by torchlight.dev')->renderFooter()->toHtml()
        );
    }

    public function test_the_documentation_article_view()
    {
        $rendered = view('hyde::components.docs.documentation-article', [
            'document' => $this->makeArticle(),
        ])->render();

        $this->assertStringContainsString('<h1>Foo</h1>', $rendered);
        $this->assertStringContainsString('<p>Hello world.</p>', $rendered);
    }

    protected function makeArticle(string $sourceFileContents = "# Foo\n\nHello world."): SemanticDocumentationArticle
    {
        $this->file('_docs/foo.md', $sourceFileContents);

        return SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));
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
