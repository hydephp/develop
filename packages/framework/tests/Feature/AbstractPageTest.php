<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Concerns\HasDynamicTitle;
use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\BladePage;
use Hyde\Framework\Models\DocumentationPage;
use Hyde\Framework\Models\MarkdownDocument;
use Hyde\Framework\Models\MarkdownPage;
use Hyde\Framework\Models\MarkdownPost;
use Hyde\Framework\Models\Parsers\MarkdownPageParser;
use Hyde\Framework\Models\Parsers\MarkdownPostParser;
use Hyde\Testing\TestCase;

/**
 * Test the AbstractPage class.
 *
 * Since the class is abstract, we can't test it directly,
 * so we will use the MarkdownPage class as a proxy,
 * since it's the simplest implementation.
 *
 * @covers \Hyde\Framework\Contracts\AbstractPage
 * @covers \Hyde\Framework\Contracts\AbstractMarkdownPage
 *
 * @backupStaticAttributes enabled
 */
class AbstractPageTest extends TestCase
{
    public function test_get_source_directory_returns_static_property()
    {
        MarkdownPage::$sourceDirectory = 'foo';
        $this->assertEquals('foo', MarkdownPage::getSourceDirectory());
    }

    public function test_get_source_directory_trims_trailing_slashes()
    {
        MarkdownPage::$sourceDirectory = '/foo/\\';
        $this->assertEquals('foo', MarkdownPage::getSourceDirectory());
    }

    public function test_get_output_directory_returns_static_property()
    {
        MarkdownPage::$outputDirectory = 'foo';
        $this->assertEquals('foo', MarkdownPage::getOutputDirectory());
    }

    public function test_get_output_directory_trims_trailing_slashes()
    {
        MarkdownPage::$outputDirectory = '/foo/\\';
        $this->assertEquals('foo', MarkdownPage::getOutputDirectory());
    }

    public function test_get_file_extension_returns_static_property()
    {
        MarkdownPage::$fileExtension = '.foo';
        $this->assertEquals('.foo', MarkdownPage::getFileExtension());
    }

    public function test_get_file_extension_forces_leading_period()
    {
        MarkdownPage::$fileExtension = 'foo';
        $this->assertEquals('.foo', MarkdownPage::getFileExtension());
    }

    public function test_get_parser_class_returns_static_property()
    {
        MarkdownPage::$parserClass = 'foo';
        $this->assertEquals('foo', MarkdownPage::getParserClass());
    }

    public function test_get_parser_returns_the_configured_parser_class()
    {
        touch(Hyde::path('_posts/foo.md'));

        MarkdownPage::$parserClass = MarkdownPostParser::class;
        $this->assertInstanceOf(MarkdownPostParser::class, MarkdownPage::getParser('foo'));

        unlink(Hyde::path('_posts/foo.md'));
    }

    public function test_get_parser_returns_instantiated_parser_for_the_supplied_slug()
    {
        touch(Hyde::path('_pages/foo.md'));

        $this->assertInstanceOf(MarkdownPageParser::class, $parser = MarkdownPage::getParser('foo'));
        $this->assertEquals('foo', $parser->get()->slug);

        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_parse_parses_supplied_slug_into_a_page_model()
    {
        touch(Hyde::path('_pages/foo.md'));

        $this->assertInstanceOf(MarkdownPage::class, $page = MarkdownPage::parse('foo'));
        $this->assertEquals('foo', $page->slug);

        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_files_returns_array_of_source_files()
    {
        touch(Hyde::path('_pages/foo.md'));
        $this->assertEquals(['foo'], MarkdownPage::files());
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_all_returns_collection_of_all_source_files_parsed_into_the_model()
    {
        touch(Hyde::path('_pages/foo.md'));
        $this->assertEquals(
            collect([new MarkdownPage([], '', '', 'foo')]),
            MarkdownPage::all()
        );
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_qualify_basename_properly_expands_basename_for_the_model()
    {
        $this->assertEquals('_pages/foo.md', MarkdownPage::qualifyBasename('foo'));
    }

    public function test_qualify_basename_trims_slashes_from_input()
    {
        $this->assertEquals('_pages/foo.md', MarkdownPage::qualifyBasename('/foo/\\'));
    }

    public function test_qualify_basename_uses_the_static_properties()
    {
        MarkdownPage::$sourceDirectory = 'foo';
        MarkdownPage::$fileExtension = 'txt';
        $this->assertEquals('foo/bar.txt', MarkdownPage::qualifyBasename('bar'));
    }

    public function test_get_output_location_returns_the_file_output_path_for_the_supplied_basename()
    {
        $this->assertEquals('foo.html', MarkdownPage::getOutputLocation('foo'));
    }

    public function test_get_output_location_returns_the_configured_location()
    {
        MarkdownPage::$outputDirectory = 'foo';
        $this->assertEquals('foo/bar.html', MarkdownPage::getOutputLocation('bar'));
    }

    public function test_get_output_location_trims_trailing_slashes_from_directory_setting()
    {
        MarkdownPage::$outputDirectory = '/foo/\\';
        $this->assertEquals('foo/bar.html', MarkdownPage::getOutputLocation('bar'));
    }

    public function test_get_output_location_trims_trailing_slashes_from_basename()
    {
        $this->assertEquals('foo.html', MarkdownPage::getOutputLocation('/foo/\\'));
    }

    public function test_get_current_page_path_returns_output_directory_and_basename()
    {
        $page = new MarkdownPage([], '', '', 'foo');
        $this->assertEquals('foo', $page->getCurrentPagePath());
    }

    public function test_get_current_page_path_returns_output_directory_and_basename_for_configured_directory()
    {
        MarkdownPage::$outputDirectory = 'foo';
        $page = new MarkdownPage([], '', '', 'bar');
        $this->assertEquals('foo/bar', $page->getCurrentPagePath());
    }

    public function test_get_current_page_path_trims_trailing_slashes_from_directory_setting()
    {
        MarkdownPage::$outputDirectory = '/foo/\\';
        $page = new MarkdownPage([], '', '', 'bar');
        $this->assertEquals('foo/bar', $page->getCurrentPagePath());
    }

    public function test_get_output_path_returns_current_page_path_with_html_extension_appended()
    {
        $page = new MarkdownPage([], '', '', 'foo');
        $this->assertEquals('foo.html', $page->getOutputPath());
    }

    // test AbstractPage implements PageContract interface
    public function test_markdown_page_implements_page_contract()
    {
        $this->assertInstanceOf(PageContract::class, new class extends AbstractPage {});
    }

    // test all page models extend AbstractPage
    public function test_all_page_models_extend_abstract_page()
    {
        $pages = [
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class
        ];

        foreach ($pages as $page) {
            $this->assertInstanceOf(AbstractPage::class, new $page());
        }

        $this->assertInstanceOf(AbstractPage::class, new BladePage('foo'));
    }

    // test all page models have configured $sourceDirectory
    public function test_all_page_models_have_configured_source_directory()
    {
        $pages = [
            BladePage::class => '_pages',
            MarkdownPage::class => '_pages',
            MarkdownPost::class => '_posts',
            DocumentationPage::class => '_docs'
        ];

        foreach ($pages as $page => $expected) {
            $this->assertEquals($expected, $page::$sourceDirectory);
        }
    }

    // test all page models have configured $outputDirectory
    public function test_all_page_models_have_configured_output_directory()
    {
        $pages = [
            BladePage::class => '',
            MarkdownPage::class => '',
            MarkdownPost::class => 'posts',
            DocumentationPage::class => 'docs'
        ];

        foreach ($pages as $page => $expected) {
            $this->assertEquals($expected, $page::$outputDirectory);
        }
    }

    // test all page models have configured $fileExtension
    public function test_all_page_models_have_configured_file_extension()
    {
        $pages = [
            BladePage::class => '.blade.php',
            MarkdownPage::class => '.md',
            MarkdownPost::class => '.md',
            DocumentationPage::class => '.md'
        ];

        foreach ($pages as $page => $expected) {
            $this->assertEquals($expected, $page::$fileExtension);
        }
    }

    // test all page models have configured $parserClass
    public function test_all_page_models_have_configured_parser_class()
    {
        $pages = [
            BladePage::class => 'Hyde\Framework\Models\BladePage',
            MarkdownPage::class => 'Hyde\Framework\Models\Parsers\MarkdownPageParser',
            MarkdownPost::class => 'Hyde\Framework\Models\Parsers\MarkdownPostParser',
            DocumentationPage::class => 'Hyde\Framework\Models\Parsers\DocumentationPageParser'
        ];

        foreach ($pages as $page => $expected) {
            $this->assertEquals($expected, $page::$parserClass);
        }
    }

    // test AbstractMarkdownPage extends AbstractPage
    public function test_abstract_markdown_page_extends_abstract_page()
    {
        $this->assertInstanceOf(AbstractPage::class, new class extends AbstractMarkdownPage {});
    }

    // test AbstractMarkdownPage implements PageContract interface
    public function test_abstract_markdown_page_implements_page_contract()
    {
        $this->assertInstanceOf(PageContract::class, new class extends AbstractMarkdownPage {});
    }

    // test AbstractMarkdownPage uses HasDynamicTitle trait
    public function test_abstract_markdown_page_uses_has_dynamic_title_trait()
    {
        $this->assertContains(HasDynamicTitle::class, class_uses_recursive(AbstractMarkdownPage::class));
    }

    // test AbstractMarkdownPage has MarkdownDocument property
    public function test_abstract_markdown_page_has_markdown_document_property()
    {
        $this->assertClassHasAttribute('markdown', AbstractMarkdownPage::class);
    }

    // test AbstractMarkdownPage has fileExtension property
    public function test_abstract_markdown_page_has_file_extension_property()
    {
        $this->assertClassHasAttribute('fileExtension', AbstractMarkdownPage::class);
    }

    // test AbstractMarkdownPage fileExtension property is set to '.md'
    public function test_abstract_markdown_page_file_extension_property_is_set_to_md()
    {
        $this->assertEquals('.md', AbstractMarkdownPage::$fileExtension);
    }

    // test AbstractMarkdownPage constructor arguments are optional
    public function test_abstract_markdown_page_constructor_arguments_are_optional()
    {
        $page = new class extends AbstractMarkdownPage {};
        $this->assertInstanceOf(AbstractMarkdownPage::class, $page); // If we get this far, we're good as no exception was thrown
    }

    // test AbstractMarkdownPage constructor assigns MarkdownDocument property if set
    public function test_abstract_markdown_page_constructor_assigns_markdown_document_property_if_set()
    {
        $document = new MarkdownDocument();
        $page = new MarkdownPage(markdownDocument: $document);
        $this->assertSame($document, $page->markdown);
    }

    // test AbstractMarkdownPage constructor creates a new MarkdownDocument if no MarkdownDocument is set
    public function test_abstract_markdown_page_constructor_creates_new_markdown_document_if_no_markdown_document_is_set()
    {
        $page = new MarkdownPage();
        $this->assertInstanceOf(MarkdownDocument::class, $page->markdown);
    }

    // test AbstractMarkdownPage markdown() helper returns the MarkdownDocument instance
    public function test_abstract_markdown_page_markdown_helper_returns_the_markdown_document_instance()
    {
        $page = new MarkdownPage();
        $this->assertSame($page->markdown, $page->markdown());
    }

    // test AbstractMarkdownPage markdown() helper returns the configured MarkdownDocument instance
    public function test_abstract_markdown_page_markdown_helper_returns_the_configured_markdown_document_instance()
    {
        $document = new MarkdownDocument();
        $page = new MarkdownPage(markdownDocument: $document);
        $this->assertSame($document, $page->markdown());
    }

    // test AbstractMarkdownPage constructs dynamic title automatically in constructor
    public function test_abstract_markdown_page_constructor_constructs_dynamic_title_automatically()
    {
        $page = new MarkdownPage(['title' => 'Foo']);
        $this->assertEquals('Foo', $page->title);
    }

    // test markdown based pages extend AbstractMarkdownPage
    public function test_markdown_based_pages_extend_abstract_markdown_page()
    {
        $pages = [
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class
        ];

        foreach ($pages as $page) {
            $this->assertInstanceOf(AbstractMarkdownPage::class, new $page());
        }
    }

    // test blade pages do not extend AbstractMarkdownPage
    public function test_blade_pages_do_not_extend_abstract_markdown_page()
    {
        $this->assertNotInstanceOf(AbstractMarkdownPage::class, new BladePage('foo'));
    }
}
