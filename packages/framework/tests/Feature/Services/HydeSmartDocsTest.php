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
use function file_put_contents;
use function str_replace;
use function unlinkIfExists;
use function view;

/**
 * @covers \Hyde\Framework\Features\Documentation\SemanticDocumentationArticle
 */
class HydeSmartDocsTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        unlinkIfExists(Hyde::path('_docs/foo.md'));
    }

    public function test_create_helper_creates_new_instance_and_processes_it()
    {
        $page = $this->makeArticle();

        $this->assertInstanceOf(SemanticDocumentationArticle::class, $page);

        $this->assertEqualsIgnoringNewlines('<p>Hello world.</p>', $page->renderBody());
    }

    public function test_instance_can_be_constructed_directly_with_same_result_as_facade()
    {
        $this->makeArticle(); // Create the file

        $class = new SemanticDocumentationArticle(DocumentationPage::parse('foo'));
        $facade = $this->makeArticle();

        // Baseline since we manually need to call the process method
        $this->assertNotEquals($class, $facade);

        $class->process();

        // Now they should be the equal
        $this->assertEquals($class, $facade);
    }

    public function test_render_header_returns_the_extracted_header()
    {
        $this->assertEqualsIgnoringNewlines('<h1>Foo</h1>', $this->makeArticle()->renderHeader());
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
            $this->assertEqualsIgnoringNewlines('<h1>Foo</h1>', $this->makeArticle()->renderHeader());
        }
    }

    public function test_render_body_returns_the_extracted_body()
    {
        $this->assertEqualsIgnoringNewlines('<p>Hello world.</p>', $this->makeArticle()->renderBody());
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
            $this->assertEqualsIgnoringNewlines('<p>Hello world.</p>', $this->makeArticle()->renderBody());
        }
    }

    public function test_render_footer_is_empty_by_default()
    {
        $this->assertEqualsIgnoringNewlines('', $this->makeArticle()->renderFooter());
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

        $page = $this->makeArticle();

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

        $page = $this->makeArticle();

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
        $this->assertStringContainsString('Syntax highlighting by <a href="https://torchlight.dev/"', $this->makeArticle('Syntax highlighted by torchlight.dev')->renderFooter()->toHtml());
    }

    public function test_the_documentation_article_view()
    {
        $rendered = view('hyde::components.docs.documentation-article', [
            'document' => $this->makeArticle(),
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

    protected function makeArticle(string $sourceFileContents = "# Foo\n\nHello world."): SemanticDocumentationArticle
    {
        file_put_contents(Hyde::path('_docs/foo.md'), $sourceFileContents);

        return SemanticDocumentationArticle::create(DocumentationPage::parse('foo'));
    }
}
