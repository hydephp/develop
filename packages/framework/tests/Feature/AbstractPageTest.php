<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Parsers\MarkdownPageParser;
use Hyde\Framework\Models\Parsers\MarkdownPostParser;
use Hyde\Testing\TestCase;
use Hyde\Framework\Models\MarkdownPage;

/**
 * Test the AbstractPage class.
 *
 * Since the class is abstract, we can't test it directly,
 * so we will use the MarkdownPage class as a proxy,
 * since it's the simplest implementation.
 *
 * @covers \Hyde\Framework\Contracts\AbstractPage
 *
 * @backupStaticAttributes enabled
 */
class AbstractPageTest extends TestCase
{
    public function test_get_source_directory_returns_static_property() {
        MarkdownPage::$sourceDirectory = 'foo';
        $this->assertEquals('foo', MarkdownPage::getSourceDirectory());
    }

    public function test_get_source_directory_trims_trailing_slashes() {
        MarkdownPage::$sourceDirectory = '/foo/\\';
        $this->assertEquals('foo', MarkdownPage::getSourceDirectory());
    }

    public function test_get_output_directory_returns_static_property() {
        MarkdownPage::$outputDirectory = 'foo';
        $this->assertEquals('foo', MarkdownPage::getOutputDirectory());
    }

    public function test_get_output_directory_trims_trailing_slashes() {
        MarkdownPage::$outputDirectory = '/foo/\\';
        $this->assertEquals('foo', MarkdownPage::getOutputDirectory());
    }

    public function test_get_file_extension_returns_static_property() {
        MarkdownPage::$fileExtension = '.foo';
        $this->assertEquals('.foo', MarkdownPage::getFileExtension());
    }

    public function test_get_file_extension_forces_leading_period() {
        MarkdownPage::$fileExtension = 'foo';
        $this->assertEquals('.foo', MarkdownPage::getFileExtension());
    }

    public function test_get_parser_class_returns_static_property() {
        MarkdownPage::$parserClass = 'foo';
        $this->assertEquals('foo', MarkdownPage::getParserClass());
    }

    public function test_get_parser_returns_the_configured_parser_class() {
        touch(Hyde::path('_posts/foo.md'));

        MarkdownPage::$parserClass = MarkdownPostParser::class;
        $this->assertInstanceOf(MarkdownPostParser::class, MarkdownPage::getParser('foo'));

        unlink(Hyde::path('_posts/foo.md'));
    }

    public function test_get_parser_returns_instantiated_parser_for_the_supplied_slug() {
        touch(Hyde::path('_pages/foo.md'));

        $this->assertInstanceOf(MarkdownPageParser::class, $parser = MarkdownPage::getParser('foo'));
        $this->assertEquals('foo', $parser->get()->slug);

        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_parse_parses_supplied_slug_into_a_page_model() {
        touch(Hyde::path('_pages/foo.md'));

        $this->assertInstanceOf(MarkdownPage::class, $page = MarkdownPage::parse('foo'));
        $this->assertEquals('foo', $page->slug);

        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_files_returns_array_of_source_files() {
        touch(Hyde::path('_pages/foo.md'));
        $this->assertEquals(['foo'], MarkdownPage::files());
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_all_returns_collection_of_all_source_files_parsed_into_the_model() {
        touch(Hyde::path('_pages/foo.md'));
        $this->assertEquals(
            collect([new MarkdownPage([], '', '', 'foo')]),
            MarkdownPage::all()
        );
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_qualify_basename_properly_expands_basename_for_the_model() {
        $this->assertEquals('_pages/foo.md', MarkdownPage::qualifyBasename('foo'));
    }

    public function test_qualify_basename_trims_slashes_from_input() {
        $this->assertEquals('_pages/foo.md', MarkdownPage::qualifyBasename('/foo/\\'));
    }

    public function test_qualify_basename_uses_the_static_properties() {
        MarkdownPage::$sourceDirectory = 'foo';
        MarkdownPage::$fileExtension = 'txt';
        $this->assertEquals('foo/bar.txt', MarkdownPage::qualifyBasename('bar'));
    }

    public function test_get_output_location_returns_the_file_output_path_for_the_supplied_basename() {
        $this->assertEquals('foo.html', MarkdownPage::getOutputLocation('foo'));
    }

    public function test_get_output_location_returns_the_configured_location() {
        MarkdownPage::$outputDirectory = 'foo';
        $this->assertEquals('foo/bar.html', MarkdownPage::getOutputLocation('bar'));
    }

    public function test_get_output_location_trims_trailing_slashes_from_directory_setting() {
        MarkdownPage::$outputDirectory = '/foo/\\';
        $this->assertEquals('foo/bar.html', MarkdownPage::getOutputLocation('bar'));
    }

    public function test_get_output_location_trims_trailing_slashes_from_basename() {
        $this->assertEquals('foo.html', MarkdownPage::getOutputLocation('/foo/\\'));
    }

    public function test_get_current_page_path_returns_output_directory_and_basename() {
        $page = new MarkdownPage([], '', '', 'foo');
        $this->assertEquals('foo', $page->getCurrentPagePath());
    }

    public function test_get_current_page_path_returns_output_directory_and_basename_for_configured_directory() {
        MarkdownPage::$outputDirectory = 'foo';
        $page = new MarkdownPage([], '', '', 'bar');
        $this->assertEquals('foo/bar', $page->getCurrentPagePath());
    }

    public function test_get_current_page_path_trims_trailing_slashes_from_directory_setting() {
        MarkdownPage::$outputDirectory = '/foo/\\';
        $page = new MarkdownPage([], '', '', 'bar');
        $this->assertEquals('foo/bar', $page->getCurrentPagePath());
    }

    public function test_get_output_path_returns_current_page_path_with_html_extension_appended() {
        $page = new MarkdownPage([], '', '', 'foo');
        $this->assertEquals('foo.html', $page->getOutputPath());
    }
}
