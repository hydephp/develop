<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Features\Documentation\SemanticDocumentationArticle;
use Hyde\Hyde;
use Hyde\Markdown\Models\Markdown;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;
use function app;
use function config;
use function str_replace;

/**
 * @covers \Hyde\Framework\Features\Documentation\SemanticDocumentationArticle
 */
class HydeSmartDocsTest extends TestCase
{
    protected DocumentationPage $page;
    protected string $html;

    protected function setUp(): void
    {
        parent::setUp();

        file_put_contents(Hyde::path('_docs/foo.md'), "# Foo\n\nHello world.");
        $this->page = DocumentationPage::parse('foo');
        $this->html = Markdown::render($this->page->markdown->body);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unlink(Hyde::path('_docs/foo.md'));
    }

    public function test_create_helper_creates_new_instance_and_processes_it()
    {
        $page = SemanticDocumentationArticle::create($this->page, $this->html);

        $this->assertInstanceOf(SemanticDocumentationArticle::class, $page);

        $this->assertEqualsIgnoringNewlines('<p>Hello world.</p>', $page->renderBody());
    }

    public function test_instance_can_be_constructed_directly_with_same_result_as_facade()
    {
        $class = new SemanticDocumentationArticle($this->page, $this->html);
        $facade = SemanticDocumentationArticle::create($this->page, $this->html);

        // Baseline since we manually need to call the process method
        $this->assertNotEquals($class, $facade);

        $class->process();

        // Now they should be the equal
        $this->assertEquals($class, $facade);
    }

    public function test_render_header_returns_the_extracted_header()
    {
        $page = SemanticDocumentationArticle::create($this->page, $this->html);

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
            $page = SemanticDocumentationArticle::create($this->page, Markdown::render($test));
            $this->assertEqualsIgnoringNewlines('<h1>Foo</h1>', $page->renderHeader());
        }
    }

    public function test_render_body_returns_the_extracted_body()
    {
        $page = SemanticDocumentationArticle::create($this->page, $this->html);

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
            $page = SemanticDocumentationArticle::create($this->page, Markdown::render($test));
            $this->assertEqualsIgnoringNewlines('<p>Hello world.</p>', $page->renderBody());
        }
    }

    public function test_render_footer_is_empty_by_default()
    {
        $page = SemanticDocumentationArticle::create($this->page, $this->html);

        $this->assertEqualsIgnoringNewlines('', $page->renderFooter());
    }

    public function test_add_dynamic_header_content_adds_source_link_when_conditions_are_met()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'header']);
        $page = SemanticDocumentationArticle::create($this->page, $this->html);

        $this->assertEqualsIgnoringNewlinesAndIndentation('<h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>', $page->renderHeader());
    }

    public function test_edit_source_link_is_added_to_footer_when_conditions_are_met()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'footer']);
        $page = SemanticDocumentationArticle::create($this->page, $this->html);

        $this->assertEqualsIgnoringNewlinesAndIndentation('<p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>', $page->renderFooter());
    }

    public function test_edit_source_link_can_be_added_to_both_header_and_footer()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'both']);
        $page = SemanticDocumentationArticle::create($this->page, $this->html);

        $this->assertEqualsIgnoringNewlinesAndIndentation('<h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>', $page->renderHeader());
        $this->assertEqualsIgnoringNewlinesAndIndentation('<p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>', $page->renderFooter());
    }

    public function test_edit_source_link_text_can_be_customized()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'both']);
        config(['docs.edit_source_link_text' => 'Go to Source']);
        $page = SemanticDocumentationArticle::create($this->page, $this->html);

        $this->assertEqualsIgnoringNewlinesAndIndentation('<h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Go to Source</a></p>', $page->renderHeader());
        $this->assertEqualsIgnoringNewlinesAndIndentation('<p class="edit-page-link"><a href="https://example.com/foo.md">Go to Source</a></p>', $page->renderFooter());
    }

    public function test_add_dynamic_footer_content_adds_torchlight_attribution_when_conditions_are_met()
    {
        $this->mockTorchlight();
        $page = SemanticDocumentationArticle::create($this->page, 'Syntax highlighted by torchlight.dev');

        $this->assertStringContainsString('Syntax highlighting by <a href="https://torchlight.dev/"', $page->renderFooter());
    }

    protected function assertEqualsIgnoringNewlines(string $expected, string $actual): void
    {
        $this->assertEquals(
            str_replace(["\n", "\r"], '', $expected),
            str_replace(["\n", "\r"], '', $actual)
        );
    }

    protected function assertEqualsIgnoringNewlinesAndIndentation(string $expected, string $actual): void
    {
        $this->assertEquals(
            str_replace(["\n", "\r", '    '], '', $expected),
            str_replace(["\n", "\r", '    '], '', $actual)
        );
    }

    protected function mockTorchlight(): void
    {
        app()->bind('env', function () {
            return 'production';
        });

        config(['torchlight.token' => '12345']);
    }
}
