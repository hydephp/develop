<?php

namespace Hyde\Framework\Testing\Feature;

use Exception;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Parsers\DocumentationPageParser;
use Hyde\Framework\Services\CollectionService;
use Hyde\Testing\TestCase;

class DocumentationPageParserTest extends TestCase
{
    public function test_can_parse_markdown_file()
    {
        file_put_contents(Hyde::path('_docs/test.md'), "# Title Heading \n\nMarkdown Content");
        $page = (new DocumentationPageParser('test'))->get();
        $this->assertInstanceOf(DocumentationPage::class, $page);
        unlink(Hyde::path('_docs/test.md'));
    }

    public function test_can_get_collection_of_slugs()
    {
        $this->resetDocs();

        file_put_contents(Hyde::path('_docs/phpunit-test.md'), "# PHPUnit Test File \n Hello World!");

        $array = CollectionService::getDocumentationPageFiles();

        $this->assertIsArray($array);
        $this->assertCount(1, $array);
        $this->assertArrayHasKey('phpunit-test', array_flip($array));
    }

    public function test_exception_is_thrown_for_missing_slug()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File _docs/invalid-file.md not found.');
        new DocumentationPageParser('invalid-file');
    }

    public function test_can_parse_documentation_page()
    {
        $parser = new DocumentationPageParser('phpunit-test');
        $this->assertInstanceOf(DocumentationPageParser::class, $parser);
    }

    public function test_title_was_inferred_from_heading()
    {
        $parser = new DocumentationPageParser('phpunit-test');
        $object = $parser->get();
        $this->assertIsString($object->title);
        $this->assertEquals('PHPUnit Test File', $object->title);
    }

    public function test_parser_contains_body_text()
    {
        $parser = new DocumentationPageParser('phpunit-test');
        $this->assertIsString($parser->body);
        $this->assertEquals("# PHPUnit Test File \n Hello World!", $parser->body);
    }

    public function test_can_get_page_model_object()
    {
        $parser = new DocumentationPageParser('phpunit-test');
        $object = $parser->get();
        $this->assertInstanceOf(DocumentationPage::class, $object);
    }

    public function test_created_model_contains_expected_data()
    {
        $parser = new DocumentationPageParser('phpunit-test');
        $object = $parser->get();
        $this->assertEquals('PHPUnit Test File', $object->title);
        $this->assertEquals("# PHPUnit Test File \n Hello World!", $object->body);
        $this->assertEquals('phpunit-test', $object->slug);
    }

    public function test_cleanup()
    {
        unlink(Hyde::path('_docs/phpunit-test.md'));
        $this->assertTrue(true);
    }

    public function test_can_get_category_from_front_matter()
    {
        file_put_contents(Hyde::path('_docs/foo.md'), "---\ncategory: foo\n---\n");
        $parser = new DocumentationPageParser('foo');
        $this->assertEquals('foo', $parser->getCategory());
        unlink(Hyde::path('_docs/foo.md'));
    }

    public function test_can_get_category_automatically_from_nested_page()
    {
        mkdir(Hyde::path('_docs/foo'));
        touch(Hyde::path('_docs/foo/bar.md'));
        $parser = new DocumentationPageParser('foo/bar');
        $this->assertEquals('foo', $parser->getCategory());

        unlink(Hyde::path('_docs/foo/bar.md'));
        rmdir(Hyde::path('_docs/foo'));
    }
}
